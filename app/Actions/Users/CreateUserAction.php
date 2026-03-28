<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateUserAction
{
    /**
     * @param array{
     *     name: string,
     *     email: string,
     *     password: string,
     *     avatar_curator_id?: int|null,
     *     email_verified_at?: string|null,
     *     roles?: array<int, string>,
     * } $data
     */
    public function handle(array $data): User
    {
        /** @var User $createdUser */
        $createdUser = DB::transaction(function () use ($data): User {
            $roles = $data['roles'] ?? null;
            unset($data['roles']);

            $user = User::query()->create($data);

            if ($roles !== null) {
                $user->syncRoles($roles);
            }

            return $user;
        });

        return $createdUser;
    }
}
