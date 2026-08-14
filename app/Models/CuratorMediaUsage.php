<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CuratorMediaUsageFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @codeCoverageIgnore
 * Simple pivot model - relationships tested via integration tests
 */
#[Guarded(['id'])]
#[Table(name: 'curator_media_usages')]
final class CuratorMediaUsage extends Model
{
    /** @use HasFactory<CuratorMediaUsageFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return BelongsTo<CuratorMedia, $this>
     */
    public function curatorMedia(): BelongsTo
    {
        return $this->belongsTo(CuratorMedia::class, 'curator_media_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
