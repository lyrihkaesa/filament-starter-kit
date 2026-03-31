<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Privacy;
use App\Models\CuratorMedia;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CuratorMediaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->can('view_any_curator::media')) {
            return true;
        }

        return $user->hasRole('super_admin');
    }

    public function view(?User $user, CuratorMedia $media): bool
    {
        // Public visibility: everyone can view
        if ($media->privacy === Privacy::PUBLIC) {
            return true;
        }

        // Member visibility: only logged in users can view
        if ($media->privacy === Privacy::MEMBER && $user instanceof User) {
            return true;
        }

        // If user is null (guest) and not public, deny
        if (! $user instanceof User) {
            return false;
        }

        // Private visibility: only creator, admin, or super_admin
        if ($user->id === $media->created_by) {
            return true;
        }

        if ($user->can('view_curator::media')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        if ($user->can('create_curator::media')) {
            return true;
        }

        return $user->hasRole('super_admin');
    }

    public function update(User $user, CuratorMedia $media): bool
    {
        if ($user->id === $media->created_by) {
            return true;
        }

        if ($user->can('update_curator::media')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('super_admin');
    }

    public function delete(User $user, CuratorMedia $media): bool
    {
        if ($user->id === $media->created_by) {
            return true;
        }

        if ($user->can('delete_curator::media')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('super_admin');
    }

    public function restore(User $user, CuratorMedia $media): bool
    {
        if ($user->id === $media->created_by) {
            return true;
        }

        if ($user->can('restore_curator::media')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('super_admin');
    }

    public function forceDelete(User $user, CuratorMedia $media): bool
    {
        if ($user->id === $media->created_by) {
            return true;
        }

        if ($user->can('force_delete_curator::media')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('super_admin');
    }

    public function replicate(User $user): bool
    {
        if ($user->can('replicate_curator::media')) {
            return true;
        }

        return $user->hasRole('super_admin');
    }

    public function reorder(User $user): bool
    {
        if ($user->can('reorder_curator::media')) {
            return true;
        }

        return $user->hasRole('super_admin');
    }
}
