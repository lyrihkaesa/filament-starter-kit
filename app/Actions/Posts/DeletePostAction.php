<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\Actions\Media\DeleteAllMediaUsagesAction;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

final readonly class DeletePostAction
{
    public function handle(Post $post): bool
    {
        return (bool) DB::transaction(function () use ($post): bool {
            resolve(DeleteAllMediaUsagesAction::class)->handle($post);

            return (bool) $post->delete();
        });
    }
}
