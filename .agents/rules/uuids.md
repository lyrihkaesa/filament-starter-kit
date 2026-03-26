---
trigger: model_decision
description: when creating migrations, models, and relationships for new tables
---

# UUIDs

For this project, always prefer UUID primary keys over auto-incrementing integers when creating new tables.

This is the default rule for agents working in this repository, including tools such as Gemini CLI, Codex, Claude, or any other coding agent that reads repository rules.

## Required Defaults

- Use UUIDs instead of auto-incrementing IDs for new tables.
- Use UUID foreign keys instead of integer foreign keys.
- Follow Laravel's native UUID support and prefer Laravel's modern UUID generation approach.

## Migration Rules

- Use `$table->uuid('id')->primary()` instead of `$table->id()`.
- Use `$table->foreignUuid('user_id')` instead of `$table->foreignId('user_id')`.
- Apply the same UUID approach consistently to related tables.

Example:

```php
$table->uuid('id')->primary();
$table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
```

## Model Rules

- Use `Illuminate\Database\Eloquent\Concerns\HasUuids`.
- Set `public $incrementing = false;`.
- Set `protected $keyType = 'string';`.

Example:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

final class Post extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';
}
```

## Laravel UUID Guidance

- Prefer Laravel's UUID handling as the baseline for this project.
- Laravel's modern UUID approach already supports UUID v7 style generation that is sortable, which is a better default than older random UUID versions for many database workloads.
- Because of that, this project prefers UUID over ULID as the main recommendation.

## ULID Note

- ULID is not forbidden, but it is not the recommended default in this project.
- If there is no strong reason to use ULID, stay with UUID.

## Legacy UUID Note

- Older UUID variants are still valid as identifiers, but they are less ideal as the default recommendation for new tables because they are not as friendly for ordered insertion and indexing patterns as newer sortable UUID approaches.
- If documenting or discussing older UUIDs, treat that as historical or compatibility context, not the preferred default for new schema design in this repository.

## Agent Instruction

When generating a new migration or model for this repository:

1. Do not use `$table->id()` unless the user explicitly asks for auto-increment IDs.
2. Do not use `$table->foreignId()` for new UUID-based relationships.
3. Default to UUID-based schema and UUID-based model configuration.
4. If the user asks for recommendations, recommend UUID over auto-increment and explain that this project prefers Laravel's sortable UUID direction over ULID.
