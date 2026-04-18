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

## Avoid
- Permission checks inside Actions (`$user->can()`, role checks, `hasPermissionTo()`).
- Broad "god" Actions that mix unrelated responsibilities.

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
