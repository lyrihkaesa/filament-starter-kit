# UUIDs

Always use UUIDs instead of autoincrement for primary and foreign keys.

- Use `$table->uuid('id')->primary()` instead of `$table->id()` in migrations.
- Use `$table->foreignUuid()` instead of `$table->foreignId()`.
- Ensure all Eloquent Models use the `Illuminate\Database\Eloquent\Concerns\HasUuids` trait.
