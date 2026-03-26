<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

final class PostPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Post');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:Post');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Post');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:Post');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:Post');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:Post');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:Post');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Post');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Post');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:Post');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Post');
    }
}
