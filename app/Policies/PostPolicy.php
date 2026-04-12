<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

final class PostPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Post');
    }

    public function view(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('View:Post') || ($authUser->id === $post->author_id && $authUser->can('ViewOwn:Post'));
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Post');
    }

    public function update(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('Update:Post') || ($authUser->id === $post->author_id && $authUser->can('UpdateOwn:Post'));
    }

    public function delete(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('Delete:Post') || ($authUser->id === $post->author_id && $authUser->can('DeleteOwn:Post'));
    }

    public function restore(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('Restore:Post') || ($authUser->id === $post->author_id && $authUser->can('RestoreOwn:Post'));
    }

    public function forceDelete(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('ForceDelete:Post') || ($authUser->id === $post->author_id && $authUser->can('ForceDeleteOwn:Post'));
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Post');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Post');
    }

    public function replicate(AuthUser $authUser, Post $post): bool
    {
        return $authUser->can('Replicate:Post');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Post');
    }
}
