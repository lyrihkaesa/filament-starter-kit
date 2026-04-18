# Action Pattern

## Use When
- Creating or changing business logic in `app/Actions/**`.

## Must
- Call Actions explicitly. Do not rely on Observers for critical business flow.
- Use `handle()` as the public entry point.
- Wrap multi-step DB writes in `DB::transaction()`.
- Keep one Action for one responsibility.
- Keep authorization outside Actions.
- Allowed auth layers: Form Request `authorize()`, Controllers, Policies, Filament Actions.
- **PASS** all necessary data (User, Request data, etc.) as parameters to `handle()`.

## Avoid
- Permission checks inside Actions (`$user->can()`, role checks, `hasPermissionTo()`).
- Broad "god" Actions that mix unrelated responsibilities.
- Global helpers/facades that imply a web/auth context: **DO NOT** use `auth()`, `Auth::user()`, `Auth::id()`, `request()`, or `session()` inside Actions.
- Actions should be "Context-Blind" so they can be run from CLI, Jobs, or Tests without issues.

## Example
```php
final class CreatePostAction
{
    public function handle(array $data): Post
    {
        return DB::transaction(function () use ($data): Post {
            return Post::query()->create($data);
        });
    }
}
```

## Filament Note
- In Filament closures (`->action()`, `->using()`), never name injected Action parameter `$action`.
- Use explicit names such as `$createPostAction`.
