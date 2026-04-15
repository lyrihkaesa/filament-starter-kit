# Activity Fix & Permission Enhancement

## Bug Description
The "Activities" button in the User Resource table redirects to a URL like:
`https://filament-starter-kit.test/app/activities?tableFilters[subject][value]=user&tableFilters[subject_id][value]=uuid`

However, the filters are not correctly applying because:
1.  The `ActivityResource` uses `subject` and `subject_id` filters.
2.  The `subject` filter logic might be missing the mapping correctly on direct URL access.
3.  The `subject_id` filter is a simple custom filter `Filter::make('subject_id')` with a form field `value`.

## Permission Requirements
- **Super Admin / Admin**: Can see all activity logs.
- **Member / Default User**: Can only see activity logs where they are the causer (`causer_id` matches their user ID).

## Implementation Steps

### 1. Scoping Query
In `ActivityResource.php`, we will override `getEloquentQuery()`:
```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    if (auth()->user()?->hasAnyRole(['super_admin', 'admin'])) {
        return $query;
    }

    return $query->where('causer_id', auth()->id());
}
```

### 2. Policy Update
In `ActivityPolicy.php`, we need to ensure `viewAny` allows members if they have the base permission.
The `ShieldSeeder` shows `member` doesn't have `ViewAny:Activity`. We should add it so they can see their own scoped logs.

### 3. Filter Fix
In `ActivityResource.php`, ensure the `subject` and `subject_id` filters are robust enough to catch URL parameters.

## Manual Testing
1. Login as `admin@example.com`.
2. Go to Users list.
3. Click "Activities" on various users.
4. Verify the list only shows activities related to that user (as subject).
5. Verify you see all records in the general Activity list.
6. Login as `member@example.com`.
7. Go to Activity list.
8. Verify you ONLY see activities where you are listed as the "Causer".
