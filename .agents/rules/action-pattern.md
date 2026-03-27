# Action Pattern & Filament Integration

When integrating an **Action Pattern** (classes in `app/Actions/...`) into a **Filament Action** (e.g., `->action()` or `->using()` closures), you MUST follow these naming conventions to avoid conflicts with Filament's internal dependency injection.

## Parameter Naming

Filament's action closures automatically inject the current `Filament\Actions\Action` instance into any parameter named `$action`. 

> [!IMPORTANT]
> **NEVER** name the injected Action Pattern service parameter `$action` within a Filament action closure.

### ❌ Incorrect
This will cause a `TypeError` because Filament will try to pass a `Filament\Actions\Action` object to a parameter expecting your custom Action class.

```php
use App\Actions\Users\CreateUserAction;
use Filament\Actions\Action;

Action::make('create')
    ->action(function (array $data, CreateUserAction $action) {
        $action->handle($data);
    })
```

### ✅ Correct
Use a descriptive name for the Action Pattern service, such as `$action[ClassName]` or simply the camelCase version of the class name.

```php
use App\Actions\Users\CreateUserAction;
use Filament\Actions\Action;

Action::make('create')
    ->action(function (array $data, CreateUserAction $createUserAction) {
        $createUserAction->handle($data);
    })
```

## Why this happens

Filament uses the `EvaluatesClosures` concern which leverages reflection. It has a prioritized list of parameters it provides to closures. For `Filament\Actions\Action`, the name `$action` is reserved for the action instance itself. If you type-hint your own class but keep the name `$action`, Filament's injection logic takes precedence by name but fails on the type check.
