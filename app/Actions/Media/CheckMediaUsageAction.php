<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\CuratorMediaUsage;

final readonly class CheckMediaUsageAction
{
    /**
     * Check if a specific media ID is being used by any model
     */
    public function handle(string $curatorMediaId): bool
    {
        return CuratorMediaUsage::query()
            ->where('curator_media_id', $curatorMediaId)
            ->exists();
    }
}
