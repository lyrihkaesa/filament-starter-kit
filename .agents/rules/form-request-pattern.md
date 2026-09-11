---
trigger: model_decision
description: when creating, modifying, or refactoring FormRequest validation and authorization classes
---

# Form Request Pattern

## Principles
- **Fail-Fast Authorization:** Always authorize before validation. Laravel executes `authorize()` before evaluating `rules()`. If unauthorized, it immediately terminates with HTTP 403, preventing wasted CPU and database resources on payload parsing and validation rules.
- **Strict Method Injection:** Always inject dependencies (User, Route Models) directly into `authorize()` and `rules()` using PHP 8 Attributes (`#[CurrentUser]`, `#[RouteParameter]`).
- **Zero Ambiguity:** Strict typing makes PHPStan level max and IDEs error-free.

## Validation & Execution Lifecycle (API Flow)
```
1. Request arrives (routes/api/v1.php)
2. FormRequest resolves:
   ├── Step 2.1: authorize() executes FIRST
   │     ├── Check Sanctum Token Ability ($user->tokenCan())
   │     └── Check Policy / Gate ($user->can())
   │     └── If false -> Abort 403 (Zero validation overhead!)
   └── Step 2.2: rules() executes ONLY if authorized
3. Controller receives validated data ($request->validated())
4. Action Pattern executes within DB::transaction
```

## Must
- **Method Injection:** Inject route parameters using `#[RouteParameter('name')]` and current user using `#[CurrentUser]`.
- **Parameter Typing:** Every injected parameter **MUST** have a strict class type hint.
- **Two-Step Authorization in `authorize()`:**
  1. Check Sanctum Token Ability (if authenticated via token).
  2. Check Policy / Gate.
  ```php
  public function authorize(
      #[RouteParameter('post')] Post $post,
      #[CurrentUser] User $user,
  ): bool {
      // 1. Sanctum Token Ability Check
      if ($user->currentAccessToken() && ! $user->tokenCan('posts:update')) {
          return false;
      }

      // 2. Policy / Gate Check
      return $user->can('update', $post);
  }
  ```
- **Use `validated()`:** In controllers, retrieve data strictly via `$request->validated()`.

## Avoid
- **DO NOT** perform authorization checks inside `rules()`.
- **DO NOT** use `$this->route('name')` or `$this->user()` manually when attributes can inject them.
- **DO NOT** use generic accessors like `$this->all()` or `$this->input()`.

## Example: Update Request
```php
final class UpdatePostRequest extends FormRequest
{
    public function authorize(
        #[RouteParameter('post')] Post $post,
        #[CurrentUser] User $user,
    ): bool {
        if ($user->currentAccessToken() && ! $user->tokenCan('posts:update')) {
            return false;
        }

        return $user->can('update', $post);
    }

    public function rules(#[RouteParameter('post')] Post $post): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', Rule::unique('posts')->ignore($post->id)],
        ];
    }
}
```

## Example: Store / Create Request
```php
final class StorePostRequest extends FormRequest
{
    public function authorize(#[CurrentUser] User $user): bool
    {
        if ($user->currentAccessToken() && ! $user->tokenCan('posts:create')) {
            return false;
        }

        return $user->can('create', Post::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }
}
```

