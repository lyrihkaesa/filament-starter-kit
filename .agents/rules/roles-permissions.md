# Roles & Permissions Guidelines

This project uses **Spatie Laravel Permission** and **Filament Shield** for access control. To ensure scalability and ease of maintenance, you MUST follow these standards.

## Core Mandates

1.  **Permission-Only Policies:** Policies MUST NOT check for Roles (e.g., `$user->hasRole()`). They MUST only check for Permissions using `$user->can()`.
2.  **Role Decoupling:** Roles are simply containers for Permissions. If an Admin and a Super Admin can both update a Post, they should both be granted the `Update:Post` permission.
3.  **Explicit Naming:** Permissions MUST follow the Filament Shield convention: `{Action}:{Model}`.

## Permission Naming Convention

| Access Level | Format | Example |
| :--- | :--- | :--- |
| **Global** | `{Action}:{Model}` | `Update:CuratorMedia` |
| **Ownership** | `{Action}Own:{Model}` | `UpdateOwn:CuratorMedia` |

## Policy Implementation Standard

Always prioritize **Global** permissions, then fallback to **Ownership** checks combined with an ownership permission.

```php
public function update(User $user, CuratorMedia $media): bool
{
    // 1. Check Global Permission (e.g. Admin/SuperAdmin)
    if ($user->can('Update:CuratorMedia')) {
        return true;
    }

    // 2. Check Ownership + Ownership Permission (e.g. Member)
    return $user->id === $media->created_by && $user->can('UpdateOwn:CuratorMedia');
}
```

## Why This Pattern?
- **Flexibility:** You can create a "Moderator" role and give them `Update:Post` without changing a single line of code.
- **Maintainability:** All authorization logic is centralized in the Policy, while access assignments are managed in the Database/UI.
- **Consistency:** Follows the established patterns used by Filament Shield and the wider Laravel ecosystem.

## Industrial Best Practice: Integrity Protection
Authorization (Permissions) should NEVER override physical data integrity. For example, even if a user has `Delete:CuratorMedia`, the Policy SHOULD return `false` if the media is still being used by other records (refer to `CheckMediaUsageAction`).
