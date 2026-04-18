<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Actions\Profile\DeleteUserAccountAction;
use App\Actions\Profile\RevokeDeviceAction;
use App\Actions\Profile\RevokeOtherDevicesAction;
use App\Actions\Profile\UpdateUserPasswordAction;
use App\Data\DeviceInfo;
use App\Filament\Forms\Components\CuratorFileUpload;
use App\Models\PersonalAccessToken;
use App\Models\User;
use DeviceDetector\DeviceDetector;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use Throwable;

/**
 * @property-read Schema $passwordForm
 */
final class EditProfile extends BaseEditProfile implements HasSchemas
{
    // @codeCoverageIgnoreStart
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
                $this->getActiveDevicesSection(),
                $this->getDeleteAccountSection(),
            ]);
    }

    /**
     * Override form to remove default actions and keep it focused on Name/Email.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                CuratorFileUpload::make('avatar_curator_id')
                    ->label(__('Avatar'))
                    ->relationship('avatarMedia', 'id')
                    ->avatar()
                    ->imageEditor()
                    ->automaticallyOpenImageEditorForAspectRatio()
                    ->imageEditorViewportWidth(320)
                    ->imageEditorViewportHeight(320)
                    ->placeholder(__('Upload avatar'))
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->directory('avatars')
                    ->visibility('public'),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                Select::make('roles')
                    ->label(__('Roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->disabled(fn (): bool => ! (Auth::user()?->can('Update:Role') ?? false)),
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

    public function savePassword(UpdateUserPasswordAction $action): void
    {
        $data = $this->passwordForm->getState();

        $user = Auth::user();

        throw_unless($user instanceof User, RuntimeException::class, 'User must be authenticated.');

        /** @var string $password */
        $password = $data['password'] ?? '';

        $action->handle($user, $password);

        $this->passwordForm->fill();

        Notification::make()
            ->title(__('Saved.'))
            ->success()
            ->send();
    }

    /**
     * Revoke a single active device (web session or API token).
     *
     * @param  string  $prefixedDeviceId  Prefixed ID: "session:{id}" or "token:{id}"
     */
    public function revokeDevice(string $prefixedDeviceId, RevokeDeviceAction $action): void
    {
        $user = Auth::user();

        throw_unless($user instanceof User, RuntimeException::class, 'User must be authenticated.');

        $action->handle($user, $prefixedDeviceId);

        Notification::make()
            ->title(__('Done.'))
            ->success()
            ->send();
    }

    /**
     * Revoke all other active devices (other web sessions + all API tokens).
     */
    public function revokeOtherDevices(string $password, RevokeOtherDevicesAction $action): void
    {
        $user = Auth::user();

        throw_unless($user instanceof User, RuntimeException::class, 'User must be authenticated.');

        $action->handle($user, $password);

        Notification::make()
            ->title(__('Done.'))
            ->success()
            ->send();
    }

    /**
     * Build a unified list of active devices from both web sessions and API tokens.
     *
     * @return Collection<int, DeviceInfo>
     */
    public function getActiveDevicesList(): Collection
    {
        /** @var Collection<int, DeviceInfo> $devices */
        $devices = collect();

        $devices = $devices->merge($this->mapSessionsToDevices());
        $devices = $devices->merge($this->mapTokensToDevices());

        return $devices->values();
    }

    public function deleteAccount(string $password, DeleteUserAccountAction $action): void
    {
        $user = Auth::user();

        if (! ($user instanceof User)) {
            return;
        }

        if (! Hash::check($password, $user->getAuthPassword())) {
            $this->addError('password', __('The password you entered is incorrect.'));

            return;
        }

        $action->handle($user, $user);

        Filament::auth()->logout();

        session()->invalidate();
        session()->regenerateToken();

        Notification::make()
            ->title(__('Account Deleted'))
            ->body(__('Your account has been scheduled for deletion. You have 30 days to restore it by contacting support.'))
            ->success()
            ->send();

        // dump('Redirecting to: ' . Filament::getLoginUrl());

        $this->redirect(Filament::getLoginUrl());
    }

    /**
     * @return array<int, string>
     */
    public function getSchemas(): array
    {
        return [
            'form',
            'passwordForm',
        ];
    }

    public function getProfileSection(): Section
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

    public function getPasswordSection(): Section
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

    public function getActiveDevicesSection(): Section
    {
        return Section::make(__('Active Devices & Sessions'))
            ->description(__('Manage and sign out your active sessions and connected devices.'))
            ->schema([
                View::make('livewire.profile.active-devices-list')
                    ->viewData(['active_devices' => $this->getActiveDevicesList()]),
            ])
            ->aside()
            ->footer([
                $this->getRevokeOtherDevicesAction(),
            ]);
    }

    public function getRevokeOtherDevicesAction(): Action
    {
        return Action::make('revokeOtherDevices')
            ->label(__('Sign Out Other Devices'))
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('Sign Out Other Devices'))
            ->modalDescription(__('Please enter your password to confirm you would like to sign out from all other devices.'))
            ->modalSubmitActionLabel(__('Sign Out Other Devices'))
            ->form([
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->currentPassword(guard: Filament::getAuthGuard()),
            ])
            ->action(function (array $data, RevokeOtherDevicesAction $actionRevoke): void {
                $password = $data['password'] ?? '';

                if (! is_string($password)) {
                    return;
                }

                $this->revokeOtherDevices($password, $actionRevoke);
            });
    }

    public function getDeleteAccountSection(): Section
    {
        return Section::make(__('Delete Account'))
            ->description(__('Once your account is deleted, you have 30 days to restore it. If not restored within 30 days, the account will be permanently deleted and you will no longer be able to log in.'))
            ->schema([
                //
            ])
            ->aside()
            ->footer([
                $this->getDeleteAccountAction(),
            ]);
    }

    public function getDeleteAccountAction(): Action
    {
        return Action::make('deleteAccount')
            ->label(__('Delete Account'))
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('Are you sure you want to delete your account?'))
            ->modalDescription(__('Please enter your password to confirm account deletion.'))
            ->modalSubmitActionLabel(__('Delete Account'))
            ->form([
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->currentPassword(guard: Filament::getAuthGuard()),
            ])
            ->action(function (array $data, DeleteUserAccountAction $deleteUserAccountAction): void {
                $password = $data['password'] ?? '';

                if (! is_string($password)) {
                    return;
                }

                $this->deleteAccount($password, $deleteUserAccountAction);
            });
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        Log::info('Updating profile', $data);
        $record->update($data);
        Log::info('Was changed: '.($record->wasChanged() ? 'true' : 'false'));

        return parent::handleRecordUpdate($record, $data);
    }

    protected function getNameFormComponent(): TextInput
    {
        /** @var TextInput $component */
        $component = parent::getNameFormComponent();

        return $component->autofocus(false);
    }

    protected function getEmailFormComponent(): TextInput
    {
        /** @var TextInput $component */
        $component = parent::getEmailFormComponent();

        return $component;
    }

    /**
     * @return Collection<int, DeviceInfo>
     */
    private function mapSessionsToDevices(): Collection
    {
        if (config('session.driver') !== 'database') {
            /** @var Collection<int, DeviceInfo> $emptyCollection */
            $emptyCollection = collect();

            return $emptyCollection;
        }

        $currentSessionId = null;

        try {
            $currentSessionId = session()->getId();
        } catch (Throwable) {
            // No session available
        }

        return DB::table('sessions')
            ->where('user_id', Auth::id())
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function (object $session) use ($currentSessionId): DeviceInfo {
                /** @var scalar $id */
                $id = $session->id ?? '';
                $sessionId = (string) $id;

                /** @var scalar $ip */
                $ip = $session->ip_address ?? '';

                $userAgent = is_string($session->user_agent) ? $session->user_agent : '';
                $parsed = $this->parseUserAgent($userAgent);

                /** @var scalar $activity */
                $activity = $session->last_activity ?? 0;

                return new DeviceInfo(
                    deviceId: 'session:'.$sessionId,
                    type: $parsed['type'],
                    label: $parsed['label'],
                    ipAddress: (string) $ip,
                    lastActiveAt: Date::createFromTimestamp((int) $activity)->diffForHumans(),
                    isCurrentDevice: $currentSessionId !== null && $sessionId === $currentSessionId,
                );
            })->values();
    }

    /**
     * @return Collection<int, DeviceInfo>
     */
    private function mapTokensToDevices(): Collection
    {
        $user = Auth::user();

        if (! ($user instanceof User)) {
            /** @var Collection<int, DeviceInfo> $emptyCollection */
            $emptyCollection = collect();

            return $emptyCollection;
        }

        return PersonalAccessToken::query()
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', $user->getMorphClass())
            ->latest('last_used_at')
            ->get()
            ->map(function (PersonalAccessToken $token): DeviceInfo {
                $userAgent = is_string($token->user_agent) ? $token->user_agent : '';
                $parsed = $this->parseUserAgent($userAgent);

                $lastActive = $token->last_used_at
                    ? $token->last_used_at->diffForHumans()
                    : $token->created_at?->diffForHumans() ?? '-';

                return new DeviceInfo(
                    deviceId: 'token:'.$token->id,
                    type: $parsed['type'],
                    label: $this->formatTokenName($token->name),
                    ipAddress: is_string($token->ip_address) ? $token->ip_address : '',
                    lastActiveAt: $lastActive,
                    isCurrentDevice: false,
                );
            })->values();
    }

    private function formatTokenName(string $name): string
    {
        $parts = explode(':', $name);

        if (count($parts) >= 3) {
            return mb_trim(implode(' ', array_slice($parts, 1)));
        }

        if (count($parts) === 2) {
            return $parts[1];
        }

        return $name;
    }

    /**
     * Parse a User-Agent string using DeviceDetector and return a normalized type and label.
     *
     * @return array{type: 'web_session'|'mobile_app'|'desktop_app'|'api_client', label: string}
     */
    private function parseUserAgent(string $userAgent): array
    {
        if ($userAgent === '') {
            return ['type' => 'api_client', 'label' => 'Unknown Device'];
        }

        $detector = new DeviceDetector($userAgent);
        $detector->parse();

        $client = $detector->getClient();
        $os = $detector->getOs();

        $clientName = is_array($client) && is_string($client['name'] ?? null) ? $client['name'] : 'Unknown';
        $osName = is_array($os) && is_string($os['name'] ?? null) ? $os['name'] : 'Unknown';

        $isBot = $detector->isBot();

        if ($isBot) {
            return ['type' => 'api_client', 'label' => $clientName];
        }

        if ($detector->isMobile()) {
            return ['type' => 'mobile_app', 'label' => sprintf('%s on %s', $clientName, $osName)];
        }

        // DeviceDetector identifies browser-based clients; non-browser clients are API clients.
        $clientType = is_array($client) && is_string($client['type'] ?? null) ? $client['type'] : '';

        if (in_array($clientType, ['browser', ''], true)) {
            return ['type' => 'web_session', 'label' => sprintf('%s on %s', $clientName, $osName)];
        }

        return ['type' => 'api_client', 'label' => sprintf('%s on %s', $clientName, $osName)];
    }

    // @codeCoverageIgnoreEnd
}
