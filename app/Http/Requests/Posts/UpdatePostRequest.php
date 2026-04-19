<?php

declare(strict_types=1);

namespace App\Http\Requests\Posts;

use App\Models\Post;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePostRequest extends FormRequest
{
    public function authorize(
        #[RouteParameter('post')] Post $post,
        #[CurrentUser] User $user
    ): bool {
        if ($user->currentAccessToken() && ! $user->tokenCan('posts:update')) {
            return false;
        }

        return $user->can('update', $post);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(#[RouteParameter('post')] Post $post): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($post->id)],
            'content' => ['sometimes', 'string'],
            'author_id' => ['sometimes', 'uuid', 'exists:users,id'],
            'thumbnail_curator_id' => ['nullable', 'uuid', 'exists:curator,id'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
