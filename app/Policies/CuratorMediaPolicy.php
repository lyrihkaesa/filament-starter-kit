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

    public function create(User $user): bool
    {
        return $user->can('Create:CuratorMedia');
    }

    public function update(User $user, CuratorMedia $media): bool
    {
        if ($user->can('Update:CuratorMedia')) {
            return true;
        }

        return $user->id === $media->created_by && $user->can('UpdateOwn:CuratorMedia');
    }

    public function delete(User $user, CuratorMedia $media): bool
    {
        if (! $this->canDeleteRecord($user, $media)) {
            return false;
        }

        if (! resolve(CheckMediaUsageAction::class)->handle((string) $media->id)) {
            return true;
        }

        return $this->canDeleteUsedRecord($user);
    }

    public function restore(User $user, CuratorMedia $media): bool
    {
        if ($user->can('Restore:CuratorMedia')) {
            return true;
        }

        return $user->id === $media->created_by && $user->can('RestoreOwn:CuratorMedia');
    }

    public function forceDelete(User $user, CuratorMedia $media): bool
    {
        if (! $this->canForceDeleteRecord($user, $media)) {
            return false;
        }

        if (! resolve(CheckMediaUsageAction::class)->handle((string) $media->id)) {
            return true;
        }

        return $this->canForceDeleteUsedRecord($user);
    }

    public function replicate(User $user): bool
    {
        return $user->can('Replicate:CuratorMedia');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:CuratorMedia');
    }

    private function canDeleteRecord(User $user, CuratorMedia $media): bool
    {
        if ($user->can('Delete:CuratorMedia')) {
            return true;
        }

        return $user->id === $media->created_by && $user->can('DeleteOwn:CuratorMedia');
    }

    private function canDeleteUsedRecord(User $user): bool
    {
        return $user->can('Delete:CuratorMedia') || $user->can('DeleteUsed:CuratorMedia');
    }

    private function canForceDeleteRecord(User $user, CuratorMedia $media): bool
    {
        if ($user->can('ForceDelete:CuratorMedia')) {
            return true;
        }

        return $user->id === $media->created_by && $user->can('ForceDeleteOwn:CuratorMedia');
    }

    private function canForceDeleteUsedRecord(User $user): bool
    {
        return $user->can('ForceDelete:CuratorMedia') || $user->can('ForceDeleteUsed:CuratorMedia');
    }
}
