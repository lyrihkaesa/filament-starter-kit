# Roles & Permissions Guidelines

## Use When
- Creating or updating Policies for Models and Filament Resources.
- Naming permissions and designing role/permission matrices.
- Implementing authorization for Filament Shield and API routes.

## Core Rules
1. **Mandatory Policy per Resource/Model:**
   - Every entity/model managed by Filament or exposed via API **MUST** have a dedicated Policy registered in Laravel.
   - Filament Shield generates and resolves policies automatically based on model conventions.
2. **Strict Permission-Only Checks (No Role Checks in Policies):**
   - Policies **MUST NOT** check roles (e.g., `hasRole('admin')` or `hasAnyRole(...)` is strictly forbidden in policies).
   - Roles are strictly containers/bundles for permissions. Always check permissions via `$user->can()` or `$user->hasPermissionTo()`.
   - *Why?* Checking roles creates tight coupling and breaks customization if an admin reconfigures role permissions in the Shield UI.
3. **Filament Shield Permission Format:**
   - Global permissions: `{Action}:{Model}` (e.g., `ViewAny:Post`, `View:Post`, `Create:Post`, `Update:Post`, `Delete:Post`).
   - Ownership permissions: `{Action}Own:{Model}` (e.g., `UpdateOwn:Post`, `DeleteOwn:Post`).

## Policy Pattern Example
```php
final class PostPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // 1. Check global permission first (aligned with Filament Shield)
        if ($user->can('Update:Post')) {
            return true;
        }

        // 2. Fallback to ownership permission if user is the author
        return $user->id === $post->created_by
            && $user->can('UpdateOwn:Post');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('Create:Post');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        if ($user->can('Delete:Post')) {
            return true;
        }

        return $user->id === $post->created_by
            && $user->can('DeleteOwn:Post');
    }
}
```

## Integrity Guard
- Authorization should not bypass data integrity rules by default.
- Example: Prevent deleting media currently referenced by posts unless an explicit override permission exists.

