<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use stdClass;
use Throwable;

/**
 * @property-read Schema $passwordForm
 */
final class EditProfile extends BaseEditProfile implements HasSchemas
{
    use InteractsWithSchemas;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $passwordData = [];

    public static function isSimple(): bool
    {
        return false;
    }

    public function mount(): void
    {
        parent::mount();

        $this->passwordForm->fill();
    }

    /**
     * Override to render sections sequentially.
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getProfileSection(),
                $this->getPasswordSection(),
                $this->getBrowserSessionsSection(),
            ]);
    }

    /**
     * Override form to remove default actions and keep it focused on Name/Email.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar')
                    ->label(__('Avatar'))
                    ->image()
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper()
                    ->directory('avatars'),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
            ])
            ->statePath('data');
    }

    public function passwordForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('current_password')
                    ->label(__('Current Password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->currentPassword(guard: Filament::getAuthGuard()),
                TextInput::make('password')
                    ->label(__('New Password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(Password::default())
                    ->same('password_confirmation'),
                TextInput::make('password_confirmation')
                    ->label(__('Confirm Password'))
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->statePath('passwordData');
    }

    public function savePassword(): void
    {
        $data = $this->passwordForm->getState();

        $user = Auth::user();

        throw_unless($user instanceof User, RuntimeException::class, 'User must be authenticated.');

        /** @var string $password */
        $password = $data['password'] ?? '';

        $user->update([
            'password' => Hash::make($password),
        ]);

        $this->passwordForm->fill();

        Notification::make()
            ->title(__('Saved.'))
            ->success()
            ->send();
    }

    public function logoutSession(string $sessionId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', $sessionId)
            ->delete();

        Notification::make()
            ->title(__('Done.'))
            ->success()
            ->send();
    }

    public function logoutOtherBrowserSessions(string $password): void
    {
        Auth::logoutOtherDevices($password);

        if (config('session.driver') === 'database') {
            $currentSessionIdRaw = null;

            try {
                $currentSessionIdRaw = session()->getId();
            } catch (Throwable) {
                // No session
            }

            DB::table('sessions')
                ->where('user_id', Auth::id())
                ->where('id', '<>', $currentSessionIdRaw ?? 'none')
                ->delete();
        }

        Notification::make()
            ->title(__('Done.'))
            ->success()
            ->send();
    }

    /**
     * @return Collection<int|string, mixed>
     */
    public function getBrowserSessionsList(): Collection
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        $currentSessionIdRaw = null;
        try {
            $currentSessionIdRaw = session()->getId();
        } catch (Throwable) {
            // No session
        }

        /** @var Collection<int, stdClass> $sessions */
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function (object $session) use ($currentSessionIdRaw): stdClass {
                /** @var stdClass $session */
                $userAgent = is_string($session->user_agent) ? $session->user_agent : '';
                $agent = $this->createAgent($userAgent);

                /** @var scalar $id */
                $id = $session->id ?? '';
                $sessionId = (string) $id;

                /** @var scalar $ip */
                $ip = $session->ip_address ?? '';
                $ipAddress = (string) $ip;

                /** @var scalar $activity */
                $activity = $session->last_activity ?? 0;
                $lastActive = Date::createFromTimestamp((int) $activity)->diffForHumans();

                $sessionObj = new stdClass();
                $sessionObj->id = $sessionId;
                $sessionObj->agent = (object) [
                    'is_desktop' => $agent['is_desktop'],
                    'platform' => $agent['platform'],
                    'browser' => $agent['browser'],
                ];
                $sessionObj->ip_address = $ipAddress;
                $sessionObj->is_current_device = $currentSessionIdRaw && $sessionId === (string) $currentSessionIdRaw;
                $sessionObj->last_active = $lastActive;

                return $sessionObj;
            })
            ->values();

        // @phpstan-ignore return.type
        return $sessions;
    }

    /**
     * @return array<int, string>
     */
    protected function getSchemas(): array
    {
        return [
            'form',
            'passwordForm',
        ];
    }

    protected function getProfileSection(): Section
    {
        return Section::make(__('Profile Information'))
            ->description(__("Update your account's profile information and email address."))
            ->schema([
                Form::make([
                    EmbeddedSchema::make('form'),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Action::make('saveProfile')
                            ->label(__('Save'))
                            ->submit('save'),
                    ]),
            ])
            ->aside();
    }

    protected function getPasswordSection(): Section
    {
        return Section::make(__('Update Password'))
            ->description(__('Ensure your account is using a long, random password to stay secure.'))
            ->schema([
                Form::make([
                    EmbeddedSchema::make('passwordForm'),
                ])
                    ->livewireSubmitHandler('savePassword')
                    ->footer([
                        Action::make('savePassword')
                            ->label(__('Save'))
                            ->submit('savePassword'),
                    ]),
            ])
            ->aside();
    }

    protected function getBrowserSessionsSection(): Section
    {
        return Section::make(__('Browser Sessions'))
            ->description(__('Manage and log out your active sessions on other browsers and devices.'))
            ->schema([
                View::make('livewire.profile.browser-sessions-list')
                    ->viewData(['browser_sessions_data' => $this->getBrowserSessionsList()]),
            ])
            ->aside()
            ->footer([
                $this->getLogoutOtherSessionsAction(),
            ]);
    }

    protected function getLogoutOtherSessionsAction(): Action
    {
        return Action::make('logoutOtherBrowserSessions')
            ->label(__('Log Out Other Browser Sessions'))
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('Log Out Other Browser Sessions'))
            ->modalDescription(__('Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.'))
            ->modalSubmitActionLabel(__('Log Out Other Browser Sessions'))
            ->form([
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->currentPassword(guard: Filament::getAuthGuard()),
            ])
            ->action(function (array $data): void {
                $password = $data['password'] ?? '';

                if (! is_string($password)) {
                    return;
                }

                $this->logoutOtherBrowserSessions($password);
            });
    }

    /**
     * @return array{is_desktop: bool, browser: string, platform: string}
     */
    protected function createAgent(string $userAgent): array
    {
        $isMobile = (bool) preg_match('/Mobile|Android|iPhone|iPad|Phone/i', $userAgent);

        $browser = 'Unknown Browser';
        if (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        }

        $platform = 'Unknown OS';
        if (preg_match('/Windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/Mac/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iPhone|iPad/i', $userAgent)) {
            $platform = 'iOS';
        }

        return [
            'is_desktop' => ! $isMobile,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }
}
