<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;

final readonly class DeleteUserAccountAction
{
    public function handle(User $user): void
    {
        $user->delete();
    }
}
