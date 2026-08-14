# AGENTS.md - Filament Starter Kit

This document provides guidelines and commands for agents working in this Laravel + Filament codebase.

## Project Overview

- **Framework**: Laravel 13 + Filament 5 + Livewire 4
- **Package Manager**: Composer (PHP), npm (JS)
- **PHP Version**: ^8.3
- **Database**: SQLite (development) or PostgreSQL
- **Testing**: Pest PHP

## Commands

### Development Server

```bash
composer run dev        # Run all dev servers (Laravel, Queue, Vite) concurrently
npm run dev             # Run Vite dev server only
php artisan serve       # Run Laravel server only
```

### Build

```bash
npm run build           # Build frontend assets
composer install        # Install PHP dependencies
```

### Testing

```bash
# Run all tests (artisan test + unit tests + lint + types + refactor)
composer test

# Run all tests with full output
composer test-full

# Run single test file
php vendor/bin/pest tests/Feature/Auth/LoginPageTest.php

# Run single test by name
php vendor/bin/pest --filter="can filter active users"

# Run tests in specific group
php vendor/bin/pest --group=feature

# Run tests with coverage
composer test:unit

# Run tests with 100% coverage requirement
composer test:unit100

# Run tests in parallel
composer test:unit:parallel

# Run type checking only
composer test:types

# Run linting only
composer test:lint

# Run refactor check only
composer test:refactor
```

### Efficient Tooling for AI Agents

When working as an AI Agent, use these formats to minimize context noise and enable faster parsing of results.

| Tool | Recommended Flag | Purpose |
| :--- | :--- | :--- |
| **Pest** | `--compact` or `--log-json <file>` | Reduces visual noise or provides raw data. |
| **PHPStan** | `--error-format=json` | Machine-readable error list. |
| **Rector** | `--output-format=json` | Detailed list of refactoring changes. |
| **Pint** | `--format=json` | Precise list of style violations. |

#### Example Usage for Agents:

```bash
# Faster analysis of type coverage
./vendor/bin/pest --type-coverage --min=100 --type-coverage-json=report.json

# Analyzing static analysis errors without table overhead
./vendor/bin/phpstan analyse --error-format=json

# Checking refactoring changes before applying
./vendor/bin/rector process --dry-run --output-format=json
```

#### Database Configuration

The project supports testing with both **PostgreSQL** (production-like) and **SQLite** (default, fast).

**PostgreSQL (Recommended)** - Use `.env.testing`:
1. Copy `.env.testing.example` to `.env.testing`.
2. Configure your database credentials.
3. Run tests:
```bash
php artisan test
```

**SQLite (Fast)** - Use `.env.sqlite.testing`:
1. Copy `.env.sqlite.testing.example` to `.env.sqlite.testing`.
2. Run tests with explicit environment:
```bash
php artisan test --env=sqlite.testing
```

**Environment files:**
- `.env.testing` - Main test configuration (usually PostgreSQL)
- `.env.sqlite.testing` - SQLite specific configuration
- `phpunit.xml` - Fallback test configuration (SQLite in-memory)

### Linting & Code Quality

```bash
composer run lint        # Run Pint (PHP) + ESLint
pint                     # Format PHP only
pint --test             # Check PHP formatting without changes
npm run lint             # Run ESLint
composer refactor        # Run Rector to auto-fix code
composer test:refactor   # Dry-run Rector
```

### Type Coverage

```bash
composer test:type-coverage   # Run Pest with type coverage (min 100%)
composer test:types           # Run PHPStan analysis
```

## Code Style Guidelines

### PHP Conventions

#### General Rules

- Always use `declare(strict_types=1);` at the top of every PHP file
- Use `final class` for all classes unless inheritance is explicitly needed
- Use `readonly` for classes that don't need state mutation
- Use `#[readonly]` attribute when appropriate
- Use constructor property promotion when possible
- Prefer `interface` return types over concrete implementations

#### Naming Conventions

- Classes: `PascalCase` (e.g., `UserFactory`, `UpdateUserAction`)
- Methods/Variables: `camelCase` (e.g., `getUserById`, `$isActive`)
- Constants: `SCREAMING_SNAKE_CASE` (e.g., `MAX_RETRY_COUNT`)
- Traits: `PascalCase` with suffix `Trait` when not obvious (e.g., `HasFactory`, not `HasFactoryTrait`)
- Database columns/tables: `snake_case`
- Boolean variables: prefix with `is`, `has`, `can`, `should` (e.g., `isActive`, `hasPermission`)

#### Class Structure (recommended order)

1. `declare(strict_types=1);` and namespace
2. Imports (grouped: external, internal, relative)
3. Class declaration with interfaces/traits
4. Constants
5. Properties
6. Constructor
7. Public methods (primary API)
8. Protected/Private methods
9. Relationships (for Models)

#### Imports

- Always use fully qualified class names for imports
- Remove unused imports (Pint handles this)
- Group imports: Laravel core, Packages, Internal app code, Relative imports
- Sort imports alphabetically within groups

#### PHPDoc

- Use `@var` annotations for complex types
- Use `@return` annotations with generic types (e.g., `@return BelongsTo<User, $this>`)
- Use `@param` annotations for array shapes
- Use `/** @var ClassName $variable */` inline when PHPStan needs help
- Prefer native return types over PHPDoc for simple cases

#### Type Declarations

- Always use strict types
- Use nullable types with `?` (e.g., `?string`)
- Use union types when appropriate (e.g., `string|int`)
- Use `mixed` only when absolutely necessary
- Cast types explicitly when needed: `(string)`, `(int)`, `is_string()`

### Error Handling

#### Exceptions

- Use specific exception classes when possible
- Use `throw_unless()` for guard clauses
- Return `null` when failure is expected and handled
- Use `abort()` or `throw` for unexpected failures

#### Validation

- Use Form Request classes for validation
- Return early on validation failures
- Use `ValidationException::withMessages()` for API responses

#### Database Transactions

- Use `DB::transaction()` for multi-step operations
- Use `$model->saveQuietly()` when bypassing events is needed

### Testing Conventions

#### File Organization

- Test files mirror `app/` structure: `tests/Feature/Actions/Auth/AuthActionsTest.php`
- Use `uses(RefreshDatabase::class);` at top of test files
- Use `beforeEach()` for setup that applies to all tests in a file

#### Test Naming

- Use `it()` for single assertions
- Use descriptive test names: `it('assigns the member role during registration when it exists')`
- Use `describe()` for grouping related tests
- Use datasets (`with()`) for testing multiple inputs

#### Test Structure

- One assertion concept per test (AAA: Arrange, Act, Assert)
- Use `expect()` for assertions
- Use `toBe()`, `toEqual()`, `toBeTrue()`, `toBeFalse()`, `toHaveCount()`, etc.
- Chain assertions: `expect($user)->not->toBeNull()->and($user->name)->toBe('Expected')`
- Use `Livewire::actingAs($user)->test(Component::class)` for Filament tests

#### Test Doubles

- Use Pest's `mock()` for mocking
- Use factories for creating test data
- Use `Storage::fake('public')` for file storage tests

### Laravel Conventions

#### Models

- Use `$guarded = ['id']` instead of `$fillable` when most fields are fillable
- Use UUIDs: `use HasUuids;`, `$keyType = 'string'`, `$incrementing = false;`
- Use soft deletes: `use SoftDeletes;`
- Define casts in `casts()` method (not property)
- Use `$hidden` for sensitive fields

#### Actions (recommended pattern)

- Put business logic in `app/Actions/` classes
- Use `final readonly class` for actions
- Single public method `handle()` with typed parameters
- Use dependency injection in constructor or handle method

#### Controllers

- Use API Resource classes for responses: `JsonResource::make($data)->response()`
- Return `JsonResponse` with appropriate status codes
- Use Form Request classes for validation

#### Routes

- API routes in `routes/api.php`
- Web routes in `routes/web.php`
- Use route model binding where possible
- Use route groups for middleware/prefixes

#### Configuration

- Use `config()` helper for settings
- Use `.env` for environment-specific values
- Use `config:cache` and `config:clear` for optimization

### Filament Conventions

#### Resources

- Use separate files for Table, Form, Infolist, and Pages
- Location: `app/Filament/Resources/{ResourceName}/{Tables,Schemas,Pages}/`
- Use `static string $model = Model::class;`

#### Tables

- Use `UsersTable::configure($table)` pattern
- Use `CuratorColumn` for media
- Use `TextColumn` with `->state(fn())` for computed values
- Use badges for status columns

#### Forms

- Use separate Schema classes
- Use `TextInput`, `Select`, `Toggle`, etc. components
- Use `->label(__('key'))` for localization

#### Pages

- Extend appropriate base class (EditRecord, ListRecords, etc.)
- Use `protected static string $resource = ResourceClass::class;`
- Override methods like `getHeaderActions()`, `handleRecordUpdate()`

### Blade/Tailwind Conventions

#### Tailwind CSS v4

- Uses `@import 'tailwindcss';` syntax
- Uses `@theme` block for customizations
- Use dark mode classes: `dark:text-white`, `dark:bg-gray-950`

#### Blade Components

- Use Blade components: `<x-component-name>`
- Use `{{ $slot }}` for component content
- Use `@props()` for component properties
- Use `@aware()` for accessing parent component data

### Architecture Rules

#### Service Layer

- Business logic goes in `app/Actions/` (recommended) or `app/Services/`
- Controllers should be thin - delegate to Actions
- Models should represent data, not contain business logic

#### Dependency Injection

- Use constructor injection for services
- Use method injection for simple, single-use dependencies
- Use `resolve()` or `$container->make()` for runtime resolution

#### Security

- Use Sanctum for API authentication
- Use Filament Shield for permissions
- Validate all user input
- Use mass assignment protection

## Quality Gates

All PRs should pass:

1. `composer test` - All tests green
2. `composer test:types` - PHPStan level max with no errors
3. `composer test:lint` - Pint formatting correct
4. `composer test:refactor` - No Rector recommendations

## Git Conventions

- Use conventional commits: `feat:`, `fix:`, `docs:`, `refactor:`, `test:`
- Keep commits atomic
- Write descriptive commit messages
- Run linting before committing (Pint is pre-configured as pre-commit hook via Pint)

## Additional Resources

- Laravel: https://laravel.com/docs
- Filament: https://filamentphp.com/docs
- Pest: https://pestphp.com
- Tailwind CSS: https://tailwindcss.com
