---
trigger: model_decision
description: when creating, modifying, or integrating Filament Resources, Pages, and Actions with backend Action classes
---

# Filament Action Integration Pattern

## Use When
- Creating or editing Filament Resource Pages (`CreateRecord`, `EditRecord`, `ListRecords`).
- Configuring table, header, or modal actions (Create, Edit, Delete).

## Core Rule
Filament Pages and Actions **MUST NOT** perform raw Eloquent mutations directly in page hooks. They **MUST** delegate all Create, Update, and Delete (CUD) operations to their corresponding Action classes in `app/Actions/**`.

## Implementation Patterns

### 1. CreateRecord Page (`Create{Model}.php`)
Override `handleRecordCreation(array $data): Model`:
```php
protected function handleRecordCreation(array $data): Model
{
    return resolve(CreatePostAction::class)->handle($data);
}
```

### 2. EditRecord Page (`Edit{Model}.php`)
Override `handleRecordUpdate(Model $record, array $data): Model`:
```php
protected function handleRecordUpdate(Model $record, array $data): Model
{
    /** @var Post $record */
    return resolve(UpdatePostAction::class)->handle($record, $data);
}
```

### 3. Delete Action (Page Header or Table Action)
Use `->using()` to delegate to the Delete Action:
```php
DeleteAction::make()
    ->using(fn (Post $record, DeletePostAction $deletePostAction): bool => $deletePostAction->handle($record));
```

## Filament Parameter Naming
- In Filament closures (`->action()`, `->using()`), **NEVER** name an injected Action parameter `$action`. Filament reserves `$action` for its own component instance.
- Always use descriptive parameter names, e.g., `$deletePostAction`, `$publishPostAction`.
