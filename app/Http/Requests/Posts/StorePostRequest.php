<?php

declare(strict_types=1);

namespace App\Http\Requests\Posts;

use App\Models\Post;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Foundation\Http\FormRequest;

final class StorePostRequest extends FormRequest
{
    public function authorize(#[CurrentUser] User $user): bool
    {
        if ($user->currentAccessToken() && ! $user->tokenCan('posts:create')) {
            return false;
        }

        return $user->can('create', Post::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug'],
            'content' => ['required', 'string'],
            'author_id' => ['required', 'uuid', 'exists:users,id'],
            'thumbnail_curator_id' => ['nullable', 'uuid', 'exists:curator,id'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}

