# Controller Pattern

## Principles
- **Clean & Lean:** Controllers must be thin coordinators. Their only job is to coordinate: Authorize -> Validate -> Execute Action -> Return Response.
- **Strict Typing:** Always use strict type accessors from the request.
- **No Inheritance:** Controllers **MUST NOT** extend the base `App\Http\Controllers\Controller` class. They should be final, standalone classes.

## Must
- **Route Model Binding:** Use explicit model binding in controller methods.
- **Parameter Order:** 
  1. `FormRequest $request` (if applicable)
  2. `{Model} $model` (the bound model from the route)
  3. `{Action} $action` (the logic handler for CUD mutations)
  4. `#[CurrentUser] User $user` (the authenticated user)
- **Authorization in Request (Fail-Fast):** Authorization logic **MUST** be placed in the `authorize()` method of the `FormRequest` using Policies, running before validation rules.
- **Dual-Context Auth:** To support both Web (Session) and API (Sanctum), inject attributes in `authorize()` and check token ability before checking policy:
  ```php
  public function authorize(
      #[RouteParameter('post')] Post $post,
      #[CurrentUser] User $user,
  ): bool {
      // 1. Check Sanctum ability if authenticated via token
      if ($user->currentAccessToken() && ! $user->tokenCan('posts:update')) {
          return false;
      }

      // 2. Check Policy / Gate
      return $user->can('update', $post);
  }
  ```
- **Authorization in Controller:** Only allowed for simple `GET` requests that do not require a `FormRequest`.
- **Injected Auth:** Use the `#[CurrentUser]` attribute to inject the authenticated user.
- **Strict Request Access:** Use type-safe methods for data retrieval:
  - `$request->string('key')`
  - `$request->integer('key')`
  - `$request->boolean('key')`
- **PHPStan Happiness:** Use `assert()` to narrow down types when needed.

## Avoid
- **Context Helpers:** DO NOT use `auth()`, `Auth::user()`, `request()`, or `session()`.
- **User Retrieval:** DO NOT use `$request->user()`. Use `#[CurrentUser] User $user`.
- **Generic Accessors:** DO NOT use `$request->input()`, `$request->all()`, or `$request->get()`.

## Example
```php
final readonly class PostController
{
    public function update(
        UpdatePostRequest $request,
        Post $post,
        UpdatePostAction $action,
        #[CurrentUser] User $user
    ): JsonResponse {
        $data = $request->validated();
        
        $post = $action->handle($post, $data);

        return (new PostResource($post))->response();
    }
}
```

