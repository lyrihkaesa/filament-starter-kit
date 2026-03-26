<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final readonly class LoginUserAction
{
    /**
     * @param  array<int, string>  $abilities
     */
    public function handle(User $user, string $password, string $deviceName, array $abilities): ?string
    {
        if (! Hash::check($password, $user->password)) {
            return null;
        }

        return $user->createToken($deviceName, $abilities)->plainTextToken;
    }
}
