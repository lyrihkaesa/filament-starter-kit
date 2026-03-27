<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;

final class Register extends BaseRegister
{
    protected static string $layout = 'layouts.auth';

    protected string $view = 'filament.pages.auth.register';
}
