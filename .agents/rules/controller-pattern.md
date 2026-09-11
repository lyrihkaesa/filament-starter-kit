---
trigger: model_decision
description: when creating, modifying, or refactoring HTTP or API controllers
---

# Controller Pattern

## Principles
- **Clean & Lean:** Controllers must be thin coordinators. Their only job is to coordinate: Authorize -> Validate -> Execute Action -> Return Response.
- **Resourceful Methods Only ("Cruddy by Design"):** Controllers **MUST ONLY** define standard Laravel resourceful methods (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`) or single-action invokable controllers (`__invoke`).
- **No Custom Action Methods:** NEVER add arbitrary action methods like `publish()`, `approve()`, `cancel()`, `ban()` to a controller. Instead, treat the action as a dedicated resource/sub-resource:
  - Create a new focused controller (e.g., `PublishPostController` or `PostPublicationController`).
  - Use standard methods such as `store` (to publish), `destroy` (to unpublish), or `__invoke`.
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
- **Default Resource Methods Only:** Any public method declared on a controller MUST strictly adhere to Pest's `preset()->laravel()` allowed public methods:
  - `__construct`
  - `__invoke`
  - `index`
  - `show`
  - `create`
  - `store`
  - `edit`
  - `update`
  - `destroy`
  - `middleware` (when implementing `HasMiddleware`)
  All helper methods must be `private`. This ensures automatic compliance with `arch()->preset()->laravel()`.
- **Sub-Resource Controller for Custom Actions:** For state changes or domain operations (e.g. publishing, verifying, restoring):
  - Model the action as a dedicated controller (e.g. `PublishPostController` or `PostPublicationController`).
  - Use `store`/`update`/`destroy` or `__invoke` instead of inventing custom method names.
- **Strict Request Access:** Use type-safe methods for data retrieval:
  - `$request->string('key')`
  - `$request->integer('key')`
  - `$request->boolean('key')`
- **PHPStan Happiness:** Use `assert()` to narrow down types when needed.

## Avoid
- **Custom Public Action Methods:** DO NOT declare arbitrary public methods like `publish()`, `approve()`, `archive()`, `ban()` in a controller.
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

