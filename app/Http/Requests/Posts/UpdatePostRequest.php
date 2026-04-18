<?php

declare(strict_types=1);

namespace App\Http\Requests\Posts;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authUser = $this->user();
        $targetPost = $this->route('post');

        return $authUser !== null
            && $targetPost instanceof Post
            && $authUser->tokenCan('posts:update')
            && $authUser->can('update', $targetPost);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Post $post */
        $post = $this->route('post');

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
