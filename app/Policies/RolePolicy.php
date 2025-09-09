<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

// @codeCoverageIgnoreStart
final class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
// @codeCoverageIgnoreEnd
