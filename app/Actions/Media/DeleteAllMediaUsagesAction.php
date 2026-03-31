<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\CuratorMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class DeleteAllMediaUsagesAction
{
    /**
     * Delete all media usage records for a specific model (on delete)
     */
    public function handle(Model $model): void
    {
        DB::transaction(function () use ($model): void {
            CuratorMediaUsage::query()
                ->where('model_type', $model->getMorphClass())
                ->where('model_id', $model->getKey())
                ->delete();
        });
    }
}
