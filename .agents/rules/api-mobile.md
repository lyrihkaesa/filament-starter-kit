---
trigger: model_decision
description: when generating or modifying Laravel API resources, controllers, requests, and versioned endpoints
---

# API Mobile Contract

## Use When
- Building or editing `/api/v1` endpoints for typed mobile clients.

## Response Contract
- Success: `message` + `data`.
- Error: `message` + `errors`.
- Optional: `meta` for pagination/capabilities.
- Never return null keys (`data: null`, `errors: null`, `meta: null`).
- Collection `data` must be a direct array.
- Single-resource `data` must be a direct object.

## Resource Serialization Rules
- Shape fields explicitly. Do not rely on default scaffold output.
- Keep type stability: UUID as string.
- Keep type stability: integer as integer.
- Keep type stability: boolean as boolean.
- Keep type stability: nullable as `null` (not empty string).
- Keep type stability: timestamps as ISO-8601 string.

## Authorization Contract (`can`)
- Item-level keys: `can.view`, `can.update`, `can.delete`.
- Collection-level key: `meta.can.create`.
- All `can` values must be boolean.
- Compute `can` in Controllers using token ability + policy/gate.
- Never compute `can` in Actions.

## Controller Rules
- Prefer final API controllers.
- Keep controller flow thin: FormRequest (token ability -> authorize -> validate) -> Action -> Response.
- Do not place Sanctum `tokenCan(...)` checks in Actions.
- Form Requests must handle token ability + policy check in `authorize()` to fail fast before validating body.

## Requests Namespace
- Reusable requests: neutral namespace like `App\Http\Requests\Auth` or `App\Http\Requests\Users`.
- Version-only behavior: keep under `App\Http\Requests\Api\V1`.

## Pagination
- Default pagination: `page`.
- Support cursor mode with `pagination=cursor`.
- Keep pagination details in top-level `meta`.
- Include `meta.pagination_type`.
- Include `has_more_pages` for both modes.

## Status Codes
- `200` read/update/logout.
- `201` created.
- `401` unauthenticated/invalid credentials.
- `403` forbidden by token ability/policy.
- `404` not found.
- `405` method not allowed.
- `422` validation failed.

## Required Follow-Up
- If API contract changes, update docs (at least `docs/07-api.md`).
- If API contract/auth shape changes, update Pest for: response shape, response types, `can` booleans, pagination meta, and auth behavior.
