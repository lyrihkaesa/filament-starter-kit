---
trigger: model_decision
description: when importing or using models from Spatie Permission, Spatie Activitylog, or Laravel Sanctum to ensure project-specific overrides are used
---

# Third-Party Package Overrides

## Use When
- Importing models from Spatie Permission, Spatie Activitylog, or Laravel Sanctum.

## Mandatory Model Mapping
- `Spatie\Permission\Models\Role` -> `App\Models\Role`
- `Spatie\Permission\Models\Permission` -> `App\Models\Permission`
- `Spatie\Activitylog\Models\Activity` -> `App\Models\Activity`
- `Laravel\Sanctum\PersonalAccessToken` -> `App\Models\PersonalAccessToken`

## Rules
- Never import vendor models in app code or tests when project overrides exist.
- Ensure package configs point to `App\Models\*` overrides.
- Keep UUID schema consistent in related migrations (UUID PK/FK).
