---
trigger: model_decision
description: When make filament resource
---

# Filament Navigation & Sorting Guidelines

## Use When
- Creating or updating Filament Resources.
- Setting navigation labels, groups, icons, and ordering.

## Rules
- Keep navigation order centralized in `App\Support\Filament\FilamentNavigation.php`.
- In each Resource, resolve order via:

```php
public static function getNavigationSort(): ?int
{
    return FilamentNavigation::sort(static::getNavigationLabel());
}
```

- Keep strict property type for group: `protected static \UnitEnum|string|null $navigationGroup`.
- Keep strict property type for icon: `protected static string|\BackedEnum|null $navigationIcon`.
- Use `getNavigationGroup()` for group assignment.
- Use `__()` for labels (navigation and UI text).
