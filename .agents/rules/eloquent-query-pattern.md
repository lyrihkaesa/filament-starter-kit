---
trigger: model_decision
description: when generating or refactoring Eloquent read/query logic in models, resources, controllers, or services
---

# Eloquent Query Pattern

## Use When
- Writing or refactoring Eloquent read/query logic.

## Query Strategy
1. Start with local scopes (`#[Scope]`) in the Model.
2. Promote to a custom builder when complexity grows.

## Use Local Scope If
- Scope count is still small (about 5 or less).
- Query is basic filtering/sorting.
- No complex joins/subqueries/grouping.

## Use Custom Builder If
- Scope count grows beyond 5.
- Query needs complex joins/subqueries/grouping.
- Query chains are reused across multiple features.

## Mandatory Rules
- Do not add a Repository layer that only mirrors Eloquent methods.
- Keep write/mutation logic in `app/Actions`, not scopes/builders.
- Keep Controllers, Filament Resources, and Livewire components thin.
- Prefer relations, eager loading, and scopes/builders before raw SQL.

## Paths
- Local scopes: `app/Models/{Model}.php`
- Custom builders: `app/Models/Builders/{Model}Builder.php`
