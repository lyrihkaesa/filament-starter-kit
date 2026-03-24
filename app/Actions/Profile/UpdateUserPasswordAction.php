<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final readonly class UpdateUserPasswordAction
{
    public function handle(User $user, string $password): void
    {
        $user->update([
            'password' => Hash::make($password),
        ]);
    }
}
