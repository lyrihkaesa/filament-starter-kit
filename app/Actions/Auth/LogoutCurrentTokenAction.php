<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;

final readonly class LogoutCurrentTokenAction
{
    public function handle(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
