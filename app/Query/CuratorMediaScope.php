<?php

declare(strict_types=1);

namespace App\Query;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * @implements Scope<Model>
 */
final class CuratorMediaScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * This scope ensures that users can only see their own media uploads unless
     * they have the 'View:CuratorMedia' permission (typically granted to Admins).
     * Super Admins are automatically handled via Shield's Gate::before.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Skip scope when running in console to avoid issues with seeders/commands.
        if (app()->runningInConsole()) {
            return;
        }

        $user = Auth::user();

        // If no user is authenticated, we don't apply ownership filtering here.
        // The Policy will handle access for guests if applicable.
        if (! $user instanceof User) {
            return;
        }

        // 1. Check if the user has permission to view ALL media.
        // This returns true for Super Admins and anyone with the 'View:CuratorMedia' permission.
        if ($user->can('View:CuratorMedia')) {
            return;
        }

        // 2. Filter by owner for users with 'ViewOwn:CuratorMedia' permission.
        // Explicitly naming the requirement: "media list only belongs to the user themselves".
        if ($user->can('ViewOwn:CuratorMedia')) {
            $builder->where('created_by', $user->id);

            return;
        }

        // 3. Fallback: If they have ViewAny (to see the list) but neither View nor ViewOwn,
        // we default to showing only their own records for security.
        $builder->where('created_by', $user->id);
    }
}
