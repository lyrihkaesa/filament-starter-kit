---
trigger: model_import, dependency_usage
description: when importing or using models from Spatie Permission, Spatie Activitylog, or Laravel Sanctum to ensure project-specific overrides are used
---

# Third-Party Package Overrides

To maintain UUID v7 consistency across the entire database, this project overrides the default models of several key packages.

## Required Overrides

AI agents MUST use the following custom models instead of the default ones provided by the vendor:

| Package | Vendor Model | Project Model (Use This) |
| :--- | :--- | :--- |
| **Spatie Permission** | `Spatie\Permission\Models\Role` | `App\Models\Role` |
| **Spatie Permission** | `Spatie\Permission\Models\Permission` | `App\Models\Permission` |
| **Spatie Activitylog** | `Spatie\Activitylog\Models\Activity` | `App\Models\Activity` |
| **Laravel Sanctum** | `Laravel\Sanctum\PersonalAccessToken` | `App\Models\PersonalAccessToken` |

## Why These Overrides?
1. **UUID v7 Support**: Vendor models often default to auto-incrementing integers. Our custom models include the `HasUuids` trait and proper `$incrementing = false` / `$keyType = 'string'` configuration.
2. **Consistency**: Ensures that all primary and foreign keys in the system are uniform (UUID v7).
3. **Optimized Indexing**: UUID v7 is sortable, providing better performance in Postgres than random UUIDs.

## Implementation Rules
- **Imports**: Never import the vendor version of these models in Controllers, Resources, or Tests.
- **Config**: Always verify that `config/permission.php`, `config/activitylog.php`, and `AppServiceProvider.php` (for Sanctum) point to the `App\Models` namespace.
- **Migrations**: Corresponding migrations for these packages must use `$table->uuid('id')->primary()` and `uuid` for foreign keys.
