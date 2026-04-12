<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\Actions\Media\SyncMediaUsageAction;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

final readonly class CreatePostAction
{
    /**
     * @param array{
     *     title: string,
     *     slug: string,
     *     content: string,
     *     author_id: string,
     *     thumbnail_curator_id?: string|null,
     *     published_at?: string|null,
     * } $data
     */
    public function handle(array $data): Post
    {
        /** @var Post $createdPost */
        $createdPost = DB::transaction(function () use ($data): Post {
            $post = Post::query()->create($data);

            if (array_key_exists('thumbnail_curator_id', $data)) {
                resolve(SyncMediaUsageAction::class)->handle($post, 'thumbnail_curator_id', $post->thumbnail_curator_id);
            }

            return $post;
        });

        return $createdPost;
    }
}
