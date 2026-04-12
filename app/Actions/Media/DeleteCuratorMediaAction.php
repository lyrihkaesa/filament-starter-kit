<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\CuratorMedia;
use Illuminate\Support\Facades\DB;

final class DeleteCuratorMediaAction
{
    public function handle(CuratorMedia $media, ?string $deleterId = null, bool $allowDeleteWhenUsed = false): bool
    {
        if ($media->isInUse() && ! $allowDeleteWhenUsed) {
            return false;
        }

        return (bool) DB::transaction(function () use ($media, $deleterId): bool {
            $media->update([
                'deleted_by' => $deleterId ?? auth()->id(),
            ]);

            return (bool) $media->delete();
        });
    }
}
