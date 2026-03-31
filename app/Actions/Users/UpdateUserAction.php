<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Actions\Media\SyncMediaUsageAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class UpdateUserAction
{
    /**
     * @param array{
     *     name?: string,
     *     email?: string,
     *     password?: string,
     *     avatar_curator_id?: string|null,
     *     email_verified_at?: string|null,
     *     roles?: array<int, string>,
     * } $data
     */
    public function handle(User $user, array $data): User
    {

        /** @var User $updatedUser */
        $updatedUser = DB::transaction(function () use ($user, $data): User {
            $roles = $data['roles'] ?? null;
            unset($data['roles']);

            $user->update($data);

            if ($roles !== null) {
                $user->syncRoles($roles);
            }

            if (array_key_exists('avatar_curator_id', $data)) {
                resolve(SyncMediaUsageAction::class)->handle($user, 'avatar_curator_id', $data['avatar_curator_id'] ?: null);
            }

            return $user->fresh() ?: $user;
        });

        return $updatedUser;
    }
}
