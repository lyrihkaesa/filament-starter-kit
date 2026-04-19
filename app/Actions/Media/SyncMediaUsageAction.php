<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\CuratorMediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final readonly class SyncMediaUsageAction
{
    /**
     * @param  Model  $model  The model using the media (e.g. Post, User)
     * @param  string  $fieldName  The field name (e.g. 'avatar_curator_id', 'thumbnail_curator_id')
     * @param  string|null  $curatorMediaId  The media ID being assigned
     */
    public function handle(Model $model, string $fieldName, ?string $curatorMediaId): void
    {
        DB::transaction(function () use ($model, $fieldName, $curatorMediaId): void {
            if (in_array($curatorMediaId, [null, '', '0'], true)) {
                // Media removed, delete usage record
                CuratorMediaUsage::query()
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->where('field_name', $fieldName)
                    ->delete();

                return;
            }

            // Update or create usage record
            CuratorMediaUsage::query()->updateOrCreate(
                [
                    'model_type' => $model->getMorphClass(),
                    'model_id' => $model->getKey(),
                    'field_name' => $fieldName,
                ],
                [
                    'curator_media_id' => $curatorMediaId,
                ]
            );
        });
    }
}
