# Form Request Pattern

## Principles
- **Strict Method Injection:** Always inject dependencies (User, Route Models) directly into the `authorize()` and `rules()` methods using PHP 8 Attributes.
- **Context Awareness:** Support both API (Sanctum) and Web (Session) contexts.
- **Zero Ambiguity:** Use attributes to eliminate `mixed` types, making PHPStan and IDEs happy.

## Must
- **Method Injection:** Inject route parameters using `#[RouteParameter('name')]` and the current user using `#[CurrentUser]`.
- **Parameter Typing:** Every injected parameter **MUST** have a class type hint.
- **Dual-Context Auth:** Use the pattern below to handle both Sanctum and Session:
  ```php
  public function authorize(
      #[RouteParameter('post')] Post $post,
      #[CurrentUser] User $user,
  ): bool {
      // 1. Sanctum Ability Check (only if token is present)
      if ($user->currentAccessToken() && !$user->tokenCan('posts:update')) {
          return false;
      }

      // 2. Policy Check
      return $user->can('update', $post);
  }
  ```

## Avoid
- **Manual Resolution:** DO NOT use `$this->route('name')` or `$this->user()` inside methods.
- **Type Casting:** Avoid manual `assert()` or `instanceof` checks if Attribute injection can handle it.
- **Generic Accessors:** Prefer `$this->string()`, `$this->integer()`, etc., for request body data.

## Example
```php
final class UpdatePostRequest extends FormRequest
{
    public function authorize(
        #[RouteParameter('post')] Post $post,
        #[CurrentUser] User $user,
    ): bool {
        if ($user->currentAccessToken() && !$user->tokenCan('posts:update')) {
            return false;
        }

        return $user->can('update', $post);
    }

    public function rules(#[RouteParameter('post')] Post $post): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', Rule::unique('posts')->ignore($post->id)],
        ];
    }
}
```
