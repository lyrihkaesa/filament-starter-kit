---
trigger: model_decision
description: When make filament resource
---

# Filament Navigation & Sorting Guidelines

## 1. Centralized Sorting

All navigation sorting is centralized in `App\Support\Filament\FilamentNavigation.php`. This allows for a "Single Source of Truth" where you can reorder the entire menu by simply moving lines in an array.

```php
// app/Support/Filament/FilamentNavigation.php
public static function sort(?string $label): ?int
{
    $navigationLabels = [
        __('Post'),
        __('Media'),
        // ... add new items here to set their order
    ];
    // ...
}
```

## 2. Strict Type Hinting (PHP 8.4+)

Filament v5 requires strict property type matching. When overriding navigation properties in a Resource, you MUST use the following type hints exactly:

- **Navigation Group:** `protected static \UnitEnum|string|null $navigationGroup`
- **Navigation Icon:** `protected static string|\BackedEnum|null $navigationIcon`

Example:

```php
final class MyResource extends Resource
{
    protected static \UnitEnum|string|null $navigationGroup = 'My Group';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Users;
}
```

## 3. Best Practices

- **Grouping:** Set `getNavigationGroup()` directly in the Resource for maximum flexibility.
- **Labeling:** Use the `__()` helper directly for labels (e.g., `__('Activity')`) to support standard Laravel `id.json` translations.
- **Sorting Implementation:**

```php
public static function getNavigationSort(): ?int
{
    return FilamentNavigation::sort(static::getNavigationLabel());
}
```
