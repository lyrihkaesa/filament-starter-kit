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
     *     avatar?: string|null,
     *     email_verified_at?: string|null,
     * } $data
     */
    public function handle(array $data): User
    {
        /** @var User $createdUser */
        $createdUser = DB::transaction(fn (): User => User::query()->create($data));

        return $createdUser;
    }
}
