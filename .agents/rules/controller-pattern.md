# Controller Pattern

## Principles
- **Clean & Lean:** Controllers must be thin. Their only job is to coordinate: Validate -> Authorize -> Execute Action -> Return Response.
- **Strict Typing:** Always use strict type accessors from the request.
- **No Inheritance:** Controllers **MUST NOT** extend the base `App\Http\Controllers\Controller` class. They should be final, standalone classes.

## Must
- **Route Model Binding:** Use explicit model binding in controller methods.
- **Parameter Order:** 
  1. `FormRequest $request` (if applicable)
  2. `{Model} $model` (the bound model from the route)
  3. `{Action} $action` (the logic handler)
  4. `#[CurrentUser] User $user` (the authenticated user)
- **Authorization in Request:** Authorization logic **MUST** be placed in the `authorize()` method of the `FormRequest` using Policies: `$this->user()->can('update', $this->route('model'))`.
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

