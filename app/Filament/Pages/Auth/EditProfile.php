<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Livewire\Profile\BrowserSessions;
use App\Livewire\Profile\UpdatePassword;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

final class EditProfile extends BaseEditProfile
{
    public static function isSimple(): bool
    {
        return false;
    }

    /**
     * @return array<Action>
     */
    public function getFormActions(): array
    {
        return [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getProfileInformationSection(),
                $this->getUpdatePasswordSection(),
                $this->getBrowserSessionsSection(),
            ]);
    }

    public function savePassword(): void
    {
        $this->dispatch('update-password')->to(UpdatePassword::class);
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

        $this->dispatch('refresh-sessions')->to(BrowserSessions::class);

        Notification::make()
            ->title(__('Done.'))
            ->success()
            ->send();
    }

    protected function getProfileInformationSection(): Component
    {
        return Section::make(__('Profile Information'))
            ->description(__('Update your account\'s profile information and email address.'))
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
            ])
            ->aside()
            ->footer([
                $this->getSaveFormAction(),
            ]);
    }

    protected function getUpdatePasswordSection(): Component
    {
        return Section::make(__('Update Password'))
            ->description(__('Ensure your account is using a long, random password to stay secure.'))
            ->schema([
                Livewire::make(UpdatePassword::class)->key('update-password-component'),
            ])
            ->aside()
            ->footer([
                $this->getUpdatePasswordAction(),
            ]);
    }

    protected function getBrowserSessionsSection(): Component
    {
        return Section::make(__('Browser Sessions'))
            ->description(__('Manage and log out your active sessions on other browsers and devices.'))
            ->schema([
                Livewire::make(BrowserSessions::class)->key('browser-sessions-component'),
            ])
            ->aside()
            ->footer([
                $this->getLogoutOtherSessionsAction(),
            ]);
    }

    protected function getUpdatePasswordAction(): Action
    {
        return Action::make('savePassword')
            ->label(__('Save'))
            ->action(fn () => $this->savePassword());
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
}
