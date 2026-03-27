<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;

final readonly class RestoreUserAccountAction
{
    public function __construct(
        // Inject dependencies here
    ) {}

    public function handle(User $user): void
    {
        $user->restore();
    }
}
