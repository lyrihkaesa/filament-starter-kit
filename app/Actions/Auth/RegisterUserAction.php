<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\Users\CreateUserAction;
use App\Models\Role;
use App\Models\User;

final readonly class RegisterUserAction
{
    public function __construct(
        private CreateUserAction $createUserAction,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function handle(array $data): User
    {
        $payload = $data;

        if (Role::query()->where('name', 'member')->exists()) {
            $payload['roles'] = ['member'];
        }

        return $this->createUserAction->handle($payload);
    }
}
