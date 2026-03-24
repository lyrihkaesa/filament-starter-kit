<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Throwable;

final class EditProfile extends BaseEditProfile implements HasForms
{
    use InteractsWithForms;

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

        Auth::user()->update([
            'password' => Hash::make($data['password']),
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
            } catch (Throwable $e) {
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

    public function getBrowserSessionsList(): Collection
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        $currentSessionIdRaw = null;
        try {
            $currentSessionIdRaw = session()->getId();
        } catch (Throwable $e) {
            // No session
        }

        return DB::table('sessions')
            ->where('user_id', Auth::id())
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) use ($currentSessionIdRaw): object {
                $agent = $this->createAgent((string) ($session->user_agent ?? ''));

                return (object) [
                    'id' => $session->id,
                    'agent' => (object) [
                        'is_desktop' => $agent['is_desktop'],
                        'platform' => $agent['platform'],
                        'browser' => $agent['browser'],
                    ],
                    'ip_address' => $session->ip_address,
                    'is_current_device' => $currentSessionIdRaw && $session->id === $currentSessionIdRaw,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];
            });
    }

    protected function getForms(): array
    {
        return [
            'form',
            'passwordForm',
        ];
    }

    protected function getProfileSection(): Component
    {
        return Section::make(__('Profile Information'))
            ->description(__('Update your account\'s profile information and email address.'))
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

    protected function getPasswordSection(): Component
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

    protected function getBrowserSessionsSection(): Component
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
            ->action(fn (array $data) => $this->logoutOtherBrowserSessions($data['password']));
    }

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
