---
trigger: api_implementation
description: when generating or modifying Laravel API resources, controllers, requests, and versioned endpoints
---

# API Mobile Contract

For this project, all coding agents must follow this API contract whenever generating or modifying Laravel API code, especially for `/api/v1` endpoints used by Flutter or other typed mobile clients.

This rule exists so generated API code stays consistent even when using commands such as `php artisan make:resource`, `php artisan make:controller`, or when an agent scaffolds API files automatically.

## Response Shape

- Use a stable JSON contract with:
  - `message` as a string
  - `data` only when there is success payload
  - `errors` only when there is an error payload
  - `meta` only when there is additional metadata such as pagination
- Do not include `data: null`, `errors: null`, or `meta: null`.
- For list endpoints:
  - `data` must be a direct array
  - pagination metadata must live in top-level `meta`
- For single-resource endpoints:
  - `data` must be a direct object

Examples:

```json
{
    "message": "User retrieved successfully.",
    "data": {
        "id": "uuid"
    }
}
```

```json
{
    "message": "Users retrieved successfully.",
    "data": [
        {
            "id": "uuid"
        }
    ],
    "meta": {
        "pagination_type": "page"
    }
}
```

## API Resource Rules

When generating a Laravel API resource:

- Do not leave the resource in plain default scaffold form if the endpoint is part of the mobile API.
- Always shape values explicitly for typed clients.
- UUID identifiers should remain strings.
- Integer values must stay integers.
- Boolean values must stay booleans.
- Nullable values should be `null`, not empty strings.
- Timestamps should be ISO-8601 strings.

## Required `can` Capabilities

For API resources used by interactive UI screens, always include final authorization booleans under `can`.

### Item-level `can`

Each API resource item should expose:

- `can.view`
- `can.update`
- `can.delete`

These values must be booleans.

Example:

```json
{
    "id": "uuid",
    "name": "Kaesa",
    "can": {
        "view": true,
        "update": false,
        "delete": false
    }
}
```

### Collection-level `meta.can`

For list endpoints, include page-level capabilities in top-level `meta.can`, for example:

- `meta.can.create`

Example:

```json
{
    "message": "Users retrieved successfully.",
    "data": [],
    "meta": {
        "pagination_type": "page",
        "can": {
            "create": true
        }
    }
}
```

### Responsibility Split for `can`

- Compute `can` values in the API controller.
- Combine:
  - Sanctum token ability checks
  - policy / gate checks
- Do not compute authorization in actions.
- Do not rely on frontend role inspection.
- API resources should receive already-computed capability data and serialize it.

## Sanctum Ability Rules

- Token ability checks belong only in API controllers.
- Do not put Sanctum `tokenCan(...)` checks in actions.
- Do not put Sanctum `tokenCan(...)` checks in shared Form Requests that may be reused by web routes.
- Form Requests may still handle reusable validation and policy-based authorization.

## Shared Request Rules

- If a Form Request can be reused by both web and API, place it in a neutral namespace such as:
  - `App\Http\Requests\Auth`
  - `App\Http\Requests\Users`
- Do not place reusable requests under `App\Http\Requests\Api\V1`.
- Only keep requests under `Api\V1` if they are truly version-specific.

## Controller Rules

- API controllers in this project should not extend `App\Http\Controllers\Controller`.
- Prefer explicit final controllers for API endpoints.
- Keep controllers thin:
  - validate request
  - check token ability
  - rely on policy / gate / Form Request auth
  - call actions
  - return API resources or JSON error responses
- For success responses, prefer Laravel resource responses with `->additional([...])` when practical.
- For error responses, return explicit JSON with the project contract.

## Pagination Rules

For list endpoints that support both page and cursor pagination:

- default to page pagination
- allow client selection through query parameter:
  - `pagination=page`
  - `pagination=cursor`
- keep `data` as a direct array
- keep pagination details in top-level `meta`
- include `meta.pagination_type`

### Page pagination meta

- `current_page`
- `per_page`
- `total`
- `last_page`
- `from`
- `to`
- `has_more_pages`

### Cursor pagination meta

- `per_page`
- `next_cursor`
- `prev_cursor`
- `has_more_pages`

## HTTP Status Code Rules

Use REST-friendly status codes:

- `200 OK` for reads, updates, logout
- `201 Created` for create/register
- `401 Unauthorized` for unauthenticated or invalid credentials
- `403 Forbidden` for denied token ability or policy
- `404 Not Found` for missing resource or route
- `405 Method Not Allowed` for wrong method on a valid endpoint
- `422 Unprocessable Entity` for validation errors

## Documentation Rule

Whenever an agent changes the API contract, it must also review and update documentation in `/docs`, especially:

- `docs/07-api.md`
- any other API/security docs impacted by the change

## Testing Rule

Whenever an agent changes API contract or authorization shape, it must update Pest tests to cover:

- JSON shape
- JSON types
- `can` booleans
- pagination shape
- auth and permission behavior

