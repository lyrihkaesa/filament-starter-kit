<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Actions\Media\DeleteAllMediaUsagesAction;
use App\Models\User;

final readonly class DeleteUserAccountAction
{
    public function handle(User $user, string|User|null $deleter = null): void
    {
        $deleterId = match (true) {
            $deleter instanceof User => $deleter->id,
            is_string($deleter) => $deleter,
            default => $user->id,
        };

        $user->forceFill([
            'deleted_by' => $deleterId,
        ])->saveQuietly();

        // Track media removal
        resolve(DeleteAllMediaUsagesAction::class)->handle($user);

        $user->delete();
    }
}
