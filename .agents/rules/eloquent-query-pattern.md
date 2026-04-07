---
trigger: model_decision
description: when generating or refactoring Eloquent read/query logic in models, resources, controllers, or services
---

# Eloquent Query Pattern

This project uses a progressive query strategy to keep code maintainable and predictable for AI agents.

## Core Rule

- Start with Eloquent local scopes (`#[Scope]`) inside the Model for simple query needs.
- Move query logic to a dedicated Custom Builder (`app/Models/Builders/*Builder.php`) when query complexity grows.

## Decision Standard

Use local scopes in the Model when:

- total scopes for the model are still small (up to 5 scopes),
- query logic is straightforward filtering/sorting,
- no heavy join/subquery composition is needed.

Move to Custom Builder when:

- model has more than 5 scopes, or
- scopes include complex joins/subqueries/grouping, or
- query chains are reused across multiple features and start making the Model noisy.

## Mandatory Conventions

- Do not introduce Repository pattern only to mirror Eloquent methods.
- Keep write/mutation business logic in `app/Actions`, not in builders/scopes.
- Keep controllers, Filament resources, and Livewire components thin: orchestrate only, do not host long query logic.
- Prefer Eloquent relations, eager loading, and scopes/builders before considering raw queries.

## Expected Paths

- Local scopes: `app/Models/{Model}.php`
- Custom builders: `app/Models/Builders/{Model}Builder.php`

## AI Agent Instruction

When asked to write query code, always choose this order:

1. Local scope in Model first.
2. Promote to Custom Builder if threshold/complexity is reached.
3. Keep query logic centralized and chainable.
