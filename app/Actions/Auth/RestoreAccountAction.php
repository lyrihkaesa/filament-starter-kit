<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;

final readonly class RestoreAccountAction
{
    /**
     * Restore a soft-deleted account by user ID.
     *
     * @return bool Returns true if the account was successfully restored.
     */
    public function handle(string|int $userId): bool
    {
        $user = User::onlyTrashed()->find($userId);

        if (! $user) {
            return false;
        }

        if ($user->isAnonymous()) {
            return false;
        }

        return (bool) $user->restore();
    }
}
