<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

final class Login extends \Filament\Auth\Pages\Login
{
    public function mount(): void
    {
        parent::mount();

        // Fill the form with the admin credentials
        if (app()->hasDebugModeEnabled()) {
            $this->form->fill([
                'email' => 'superadmin@example.com',
                'password' => 'password',
                'remember' => true,
            ]);
        }
    }
}
