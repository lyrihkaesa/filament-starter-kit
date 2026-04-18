---
trigger: model_decision
description: when creating migrations, models, and relationships for new tables
---

# UUIDs

## Use When
- Creating new migrations, models, and relationships.

## Default Standard
- Use UUID (project preference: UUID v7 style) for new primary keys.
- Use UUID foreign keys for related tables.
- Do not use integer IDs unless explicitly requested.

## Migration Rules
- Use `$table->uuid('id')->primary()` instead of `$table->id()`.
- Use `$table->foreignUuid('user_id')` instead of `$table->foreignId('user_id')`.
- Keep UUID usage consistent across related tables.

## Model Rules
- Use `HasUuids`.
- Set `public $incrementing = false;`.
- Set `protected $keyType = 'string';`.

## Recommendation Note
- UUID is the default recommendation for this starter kit.
- ULID is allowed but not the default unless there is a clear reason.
