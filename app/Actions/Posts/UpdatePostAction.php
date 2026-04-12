<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\Actions\Media\SyncMediaUsageAction;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

final readonly class UpdatePostAction
{
    /**
     * @param array{
     *     title?: string,
     *     slug?: string,
     *     content?: string,
     *     author_id?: string,
     *     thumbnail_curator_id?: string|null,
     *     published_at?: string|null,
     * } $data
     */
    public function handle(Post $post, array $data): Post
    {
        /** @var Post $updatedPost */
        $updatedPost = DB::transaction(function () use ($post, $data): Post {
            $post->update($data);

            if (array_key_exists('thumbnail_curator_id', $data)) {
                resolve(SyncMediaUsageAction::class)->handle($post, 'thumbnail_curator_id', $post->thumbnail_curator_id);
            }

            return $post->fresh() ?? $post;
        });

        return $updatedPost;
    }
}
