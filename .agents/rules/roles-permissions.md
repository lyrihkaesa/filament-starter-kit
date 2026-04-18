# Roles & Permissions Guidelines

## Use When
- Creating Policies.
- Naming permissions.
- Designing role/permission matrices with Spatie Permission + Filament Shield.

## Rules
- Policies must check permissions (`$user->can()`), not roles (`$user->hasRole()`).
- Roles are permission containers only.
- Global permission format: `{Action}:{Model}` (example: `Update:Post`).
- Ownership permission format: `{Action}Own:{Model}` (example: `UpdateOwn:Post`).

## Policy Pattern
- Check global permission first.
- Fallback to ownership + ownership permission when required.

```php
public function update(User $user, Post $post): bool
{
    if ($user->can('Update:Post')) {
        return true;
    }

    return $user->id === $post->created_by
        && $user->can('UpdateOwn:Post');
}
```

## Integrity Guard
- Authorization should not bypass integrity rules by default.
- Example: block deleting media that is still in use, unless explicit override permission exists.
