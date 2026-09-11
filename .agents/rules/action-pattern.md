---
trigger: model_decision
description: when creating, modifying, or refactoring business mutation logic (Create, Update, Delete) in app/Actions
---

# Action Pattern

## Use When
- Creating, updating, or deleting business entities in `app/Actions/**`.

## Core Principles
1. **Mandatory Action Pattern for CUD:**
   - **Create, Update, Delete (CUD)** operations **MUST ALWAYS** use dedicated Action classes (`app/Actions/**`).
   - Read / Query / Search operations **DO NOT** use Action classes; use Eloquent queries and Local Scopes directly (see `eloquent-query-pattern.md`).
2. **No Observer or Event Magic for Business Mutations:**
   - **DO NOT** use Model Observers or Eloquent Event Listeners for mutating business logic, triggering side effects, or updating related tables.
   - *Rationale:* Observers and Model Events introduce hidden "magic" side effects, make code hard to trace and debug (especially for beginners), create unpredictable behavior during tests/seeders, and hinder maintainability.
   - All mutations must be explicit and discoverable via Action calls.

## Execution & Validation Flows

### 1. Web Filament Flow
```
Filament Page/Resource
  └──> Filament Action
         └──> Gate / Policy Check (authorized)
                └──> Action Pattern (app/Actions/*)
                       └──> Database Transaction (DB::transaction)
```

### 2. API Flow (Sanctum)
```
routes/api/v1.php
  └──> Controller
         └──> FormRequest
                ├──> 1. authorize() (Fail-fast: halts before validating body if forbidden)
                │      ├──> Check Sanctum Token Ability ($user->tokenCan())
                │      └──> Check Policy / Gate ($user->can())
                ├──> 2. rules() (Validation executed ONLY after authorization succeeds)
                └──> Controller receives validated data ($request->validated())
                       └──> Action Pattern (app/Actions/*)
                              └──> Database Transaction (DB::transaction)
```

## Must
- Call Actions explicitly. Never hide CUD side-effects in Observers.
- Use `handle()` as the single public entry point.
- Wrap all state-changing database writes in `DB::transaction()`.
- Keep one Action for one responsibility (single responsibility principle).
- Keep authorization **outside** Actions (in Form Requests, Controllers, Policies, or Filament Actions).
- Pass all required data (User, validated inputs, models) explicitly as parameters to `handle()`.
- Actions must be **Context-Blind**: never use `auth()`, `Auth::user()`, `request()`, or `session()`.

## Avoid
- Role checks or permission checks inside Actions (`$user->can()`, `hasRole()`).
- Using Model Observers or Model Events for business logic.
- Using Action classes for simple data retrieval/querying.
- Broad "god" Actions that mix unrelated domain tasks.

## Example
```php
final readonly class CreatePostAction
{
    public function handle(User $author, array $data): Post
    {
        return DB::transaction(function () use ($author, $data): Post {
            return $author->posts()->create($data);
        });
    }
}
```

## Filament Integration Note
- In Filament closures (`->action()`, `->using()`), never name injected Action parameters `$action` (reserved by Filament).
- Use explicit descriptive names, e.g., `$createPostAction`.

