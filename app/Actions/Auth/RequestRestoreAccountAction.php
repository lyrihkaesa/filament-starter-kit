<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\Auth\RestoreAccountNotification;

final readonly class RequestRestoreAccountAction
{
    /**
     * Send an account restoration notification to a soft-deleted user.
     *
     * @return bool Returns true if a restoration notification was successfully sent.
     */
    public function handle(string|User $user): bool
    {
        if (is_string($user)) {
            $user = User::onlyTrashed()->where('email', $user)->first();
        }

        if (! $user instanceof User) {
            return false;
        }

        if (! $user->trashed() || $user->isAnonymous()) {
            return false;
        }

        $user->notify(new RestoreAccountNotification);

        return true;
    }
}
