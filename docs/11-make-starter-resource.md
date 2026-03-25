# ✨ Feature: `make:starter-resource` — Custom Filament Resource Generator with Action Pattern

### Context for AI Agent

This issue is part of a **Laravel + Filament v3 starter kit**. The goal is to create a custom Artisan command that generates Filament Resource files using project-specific stubs — integrating the **action pattern** (as popularized by `nunomaduro/laravel-actions` or similar single-responsibility action classes).

The generated files must **not** override or conflict with Filament's built-in `make:filament-resource` command.

---

### Problem Statement

`php artisan make:filament-resource` generates standard Filament resource files without any action pattern integration. Developers must manually wire `handleRecordCreation` and `handleRecordUpdate` to dedicated Action classes every time. This is repetitive, inconsistent, and not enforced by the starter kit.

---

### Expected Behavior

Running the new command:

```bash
php artisan make:starter-resource User
```

Must produce the following files:

| File                   | Location                                     |
| ---------------------- | -------------------------------------------- |
| `UserResource.php`     | `app/Filament/Resources/`                    |
| `CreateUser.php`       | `app/Filament/Resources/UserResource/Pages/` |
| `EditUser.php`         | `app/Filament/Resources/UserResource/Pages/` |
| `CreateUserAction.php` | `app/Actions/User/` _(skip if exists)_       |
| `UpdateUserAction.php` | `app/Actions/User/` _(skip if exists)_       |

---

### Command Specification

**Signature:**

```bash
php artisan make:starter-resource {model}
    {--view              : Also generate ViewRecord page}
    {--soft-deletes      : Add soft delete support to stubs}
    {--force             : Overwrite existing files}
    {--namespace=        : Override default resource namespace}
    {--panel=            : Target a specific Filament panel}
```

**Location:** `app/Console/Commands/MakeStarterResource.php`

---

### Stub Specifications

**Stub directory:**

```
stubs/starter-kit/resource/
├── create.stub
├── edit.stub
├── view.stub       ← only when --view is passed
└── manage.stub
```

**`create.stub` must render as:**

```php
<?php

namespace {{ namespace }}\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use {{ resourceFqn }};
use App\Actions\{{ model }}\Create{{ model }}Action;

class Create{{ model }} extends CreateRecord
{
    protected static string $resource = {{ resourceFqn }}::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(Create{{ model }}Action::class)->handle($data);
    }
}
```

**`edit.stub` must render as:**

```php
<?php

namespace {{ namespace }}\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use {{ resourceFqn }};
use App\Actions\{{ model }}\Update{{ model }}Action;

class Edit{{ model }} extends EditRecord
{
    protected static string $resource = {{ resourceFqn }}::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(Update{{ model }}Action::class)->handle($record, $data);
    }
}
```

**Action stub (`action.stub`) must render as:**

```php
<?php

namespace App\Actions\{{ model }};

use App\Models\{{ model }};

class {{ actionName }}
{
    public function handle(array $data): {{ model }}
    {
        // TODO: implement
    }
}
```

---

### Constraints & Rules

1. **Do not modify** any file under `vendor/` or any Filament core class.
2. **Do not register** the new command by overriding Filament's command — register it independently in `app/Providers/AppServiceProvider.php` or a dedicated service provider.
3. Action classes must only be created if the file **does not already exist** (no `--force` override for actions unless explicitly passed).
4. All stub variables use `{{ double_curly }}` syntax, consistent with Laravel's default stub system.
5. The command must be **fully testable** — no direct static calls or unresolvable dependencies.

---

### Acceptance Criteria

- [x] `php artisan make:starter-resource User` generates all 4 files listed in the expected behavior table
- [x] Re-running the command without `--force` does **not** overwrite existing files
- [x] `--view` flag generates an additional `ViewUser.php` page with no action wiring (view only)
- [x] `--soft-deletes` flag injects soft delete trait/scope into the resource stub
- [x] Generated Action classes are skipped (not overwritten) if already present
- [x] All behavior is covered by **Pest tests** in `tests/Feature/Console/Commands/MakeStarterResourceTest.php`
- [x] Usage documented in `README.md` and `docs/11-make-starter-resource.md`

---

### Out of Scope

- Generating `form()` or `table()` column definitions (left to developer)
- Generating migrations or model files
- Modifying or wrapping `make:filament-resource`
