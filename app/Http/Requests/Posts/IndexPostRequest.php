<?php

declare(strict_types=1);

namespace App\Http\Requests\Posts;

use App\Models\Post;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Foundation\Http\FormRequest;

final class IndexPostRequest extends FormRequest
{
    public function authorize(#[CurrentUser] User $user): bool
    {
        if ($user->currentAccessToken() && ! $user->tokenCan('posts:read')) {
            return false;
        }

        return $user->can('viewAny', Post::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pagination' => ['required', 'string', 'in:page,cursor'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'cursor' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'pagination' => $this->input('pagination', 'page'),
        ]);
    }
}
