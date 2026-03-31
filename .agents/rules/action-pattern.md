# Action Pattern Guidelines

This project uses an explicit **Action Pattern** to handle business logic. This ensures logic is centralized, reusable, and easy to debug.

## Core Principles

1.  **Explicit Execution:** Actions MUST be called explicitly. Avoid using Observers for critical business logic to prevent "magic" side effects.
2.  **Standard Method:** Every Action class MUST use `handle()` as its primary entry point.
3.  **Data Integrity:** Actions that perform multiple database operations MUST wrap the logic within a `DB::transaction()`.
4.  **Single Responsibility:** Each Action should do one thing well (e.g., `SyncMediaUsageAction`, `DeleteUserAccountAction`).

## Implementation Standard

```php
namespace App\Actions\Media;

use Illuminate\Support\Facades\DB;

final class ExampleAction
{
    /**
     * Use handle() as the standard method name.
     */
    public function handle(mixed $data): void
    {
        DB::transaction(function () use ($data): void {
            // Business logic here...
        });
    }
}
```

## Filament Integration

When using Actions within Filament closures (e.g., `->action()` or `->using()`), you MUST NOT name the parameter `$action` to avoid conflicts with Filament's internal `$action` object.

### ❌ Incorrect
```php
->action(function (array $data, CreateUserAction $action) {
    $action->handle($data);
})
```

### ✅ Correct
```php
->action(function (array $data, CreateUserAction $createUserAction) {
    $createUserAction->handle($data);
})
```

## Why Explicit Actions?
- **Debuggable:** Stack traces show exactly where the logic failed.
- **Testable:** Easy to unit test in isolation.
- **Atomic:** Transactions ensure data is never left in a partial state.
- **Transparent:** Developers can see exactly what happens when a command is executed.
