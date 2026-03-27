<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;

final readonly class DeleteUserAccountAction
{
    public function __construct(
        // Inject dependencies here
    ) {}

    public function handle(User $user): void
    {
        $user->delete();
    }
}
