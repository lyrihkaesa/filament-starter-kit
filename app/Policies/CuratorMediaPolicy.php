<?php

declare(strict_types=1);

namespace App\Policies;

use App\Actions\Media\CheckMediaUsageAction;
use App\Enums\Privacy;
use App\Models\CuratorMedia;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CuratorMediaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // To see the media list, user must have the ViewAny permission.
        return $user->can('ViewAny:CuratorMedia');
    }

    public function view(?User $user, CuratorMedia $media): bool
    {
        // 1. Public privacy: everyone can view (even guests if applicable)
        if ($media->privacy === Privacy::PUBLIC) {
            return true;
        }

        // 2. Member privacy: only logged in users can view
        if ($media->privacy === Privacy::MEMBER && $user instanceof User) {
            return true;
        }

        // If guest and not public/member, deny
        if (! $user instanceof User) {
            return false;
        }

        // 3. Admin access (View All)
        if ($user->can('View:CuratorMedia')) {
            return true;
        }

        // 4. Owner access (View Own)
        return $user->id === $media->created_by && $user->can('ViewOwn:CuratorMedia');
    }

    /**
     * @codeCoverageIgnore
     */
    public function create(User $user): bool
    {
        return $user->can('Create:CuratorMedia');
    }

    /**
     * @codeCoverageIgnore
     */
    public function update(User $user, CuratorMedia $media): bool
    {
        if ($user->can('Update:CuratorMedia')) {
            return true;
        }

        return $user->id === $media->created_by && $user->can('UpdateOwn:CuratorMedia');
    }

    /**
     * @codeCoverageIgnore
     */
    public function delete(User $user, CuratorMedia $media): bool
    {
        // INDUSTRIAL BEST PRACTICE: Physical protection first
        if (resolve(CheckMediaUsageAction::class)->handle((string) $media->id)) {
            return false;
        }

        if ($user->can('Delete:CuratorMedia')) {
            return true;
        }

        return $user->id === $media->created_by && $user->can('DeleteOwn:CuratorMedia');
    }

    /**
     * @codeCoverageIgnore
     */
    public function restore(User $user, CuratorMedia $media): bool
    {
        if ($user->can('Restore:CuratorMedia')) {
            return true;
        }

        return $user->id === $media->created_by && $user->can('RestoreOwn:CuratorMedia');
    }

    /**
     * @codeCoverageIgnore
     */
    public function forceDelete(User $user, CuratorMedia $media): bool
    {
        if (resolve(CheckMediaUsageAction::class)->handle((string) $media->id)) {
            return false;
        }

        if ($user->can('ForceDelete:CuratorMedia')) {
            return true;
        }

        return $user->id === $media->created_by && $user->can('ForceDeleteOwn:CuratorMedia');
    }

    /**
     * @codeCoverageIgnore
     */
    public function replicate(User $user): bool
    {
        return $user->can('Replicate:CuratorMedia');
    }

    /**
     * @codeCoverageIgnore
     */
    public function reorder(User $user): bool
    {
        return $user->can('Reorder:CuratorMedia');
    }
}
