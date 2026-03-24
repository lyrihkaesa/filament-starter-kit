<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\On;
use Livewire\Component;

final class UpdatePassword extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('current_password')
                    ->label(__('Current Password'))
                    ->password()
                    ->revealable(Filament::arePasswordsRevealable())
                    ->rules(['required'])
                    ->currentPassword(guard: Filament::getAuthGuard()),
                TextInput::make('password')
                    ->label(__('New Password'))
                    ->password()
                    ->revealable(Filament::arePasswordsRevealable())
                    ->rules(['required'])
                    ->rule(Password::default())
                    ->same('password_confirmation'),
                TextInput::make('password_confirmation')
                    ->label(__('Confirm Password'))
                    ->password()
                    ->revealable(Filament::arePasswordsRevealable())
                    ->rules(['required']),
            ])
            ->statePath('data');
    }

    #[On('update-password')]
    public function updatePassword(): void
    {
        $data = $this->form->getState();

        Auth::user()->update([
            'password' => Hash::make($data['password']),
        ]);

        $this->form->fill();

        Notification::make()
            ->title(__('Saved.'))
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.profile.update-password');
    }
}
