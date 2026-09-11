---
trigger: model_decision
description: when generating or refactoring Eloquent read/query logic in models, resources, controllers, or services
---

# Eloquent Query Pattern

## Use When
- Writing, refactoring, or optimizing read/query and search logic.

## Query vs Action Separation
- **Queries DO NOT use Action Pattern:** Fetching, filtering, searching, or paginating records must be done directly via Eloquent Query Builder or Local Scopes.
- **Why?** Encapsulating query logic in scopes/builders makes them modular and composable. If the underlying search or read mechanism evolves later (e.g., integrating Elasticsearch, Meilisearch, or cross-database read replicas), you only swap or adapt the query scopes/builders without restructuring business mutation Actions.
- **Mutations (CUD) ALWAYS use Action Pattern:** Writes (Create, Update, Delete) are strictly handled in `app/Actions/**`.

## Query Strategy
1. **Start with Local Scopes (`#[Scope]`)** in the Model for simple filters and search criteria.
2. **Promote to a Custom Builder** (`app/Models/Builders/{Model}Builder.php`) when query logic, search criteria, or joins grow in complexity.

## Use Local Scope If
- Scope count is small (about 5 or less).
- Query is basic filtering, sorting, or simple text search.
- No complex joins, correlated subqueries, or intricate grouping.

## Use Custom Builder If
- Scope count grows beyond 5.
- Query needs complex joins, subqueries, search index adapters, or grouping.
- Query chains are reused across multiple controllers, API endpoints, or Filament tables.

## Mandatory Rules
- **No Action Pattern for Read/Query:** Never wrap a simple `select` or `find` in an Action class.
- **No Mirroring Repository Layer:** Do not add a Repository layer that merely forwards calls to Eloquent methods.
- **Keep Controllers & Resources Thin:** Delegate query logic to scopes/builders.
- **Eager Loading & N+1 Prevention:** Always use `with()` or `loadMissing()`; avoid N+1 queries.

## Paths
- Local scopes: `app/Models/{Model}.php`
- Custom builders: `app/Models/Builders/{Model}Builder.php`
