<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class CuratorMediaUsage extends Model
{
    /** @use HasFactory<\Database\Factories\CuratorMediaUsageFactory> */
    use HasFactory;

    use HasUuids;

    protected $table = 'curator_media_usages';

    protected $guarded = ['id'];

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
