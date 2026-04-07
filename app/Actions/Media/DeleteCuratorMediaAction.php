<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\CuratorMedia;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteCuratorMediaAction
{
    public function handle(CuratorMedia $media, User|string|null $deleter = null): bool
    {
        if ($media->isInUse()) {
            return false;
        }

        return (bool) DB::transaction(function () use ($media, $deleter): bool {
            $deleterId = match (true) {
                $deleter instanceof User => $deleter->id,
                is_string($deleter) => $deleter,
                default => auth()->id(),
            };

            $media->update([
                'deleted_by' => $deleterId,
            ]);

            return (bool) $media->delete();
        });
    }
}
