<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Privacy;
use App\Query\CuratorMediaScope;
use Awcodes\Curator\Models\Media;
use Database\Factories\CuratorMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * @property Privacy $privacy
 * @property string $created_by
 */
#[Fillable([
    'disk',
    'directory',
    'visibility',
    'name',
    'path',
    'width',
    'height',
    'size',
    'type',
    'ext',
    'alt',
    'title',
    'description',
    'caption',
    'pretty_name',
    'exif',
    'curations',
    'tenant_id',
    'created_by',
    'privacy',
    'deleted_by',
])]
#[WithoutIncrementing]
final class CuratorMedia extends Media
{
    /** @use HasFactory<CuratorMediaFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $keyType = 'string';

    /**
     * @return Attribute<string, never>
     */
    public function url(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $storage = Storage::disk($this->disk);

                if ($this->visibility === 'public') {
                    return $storage->url($this->path);
                }

                try {
                    return $storage->temporaryUrl($this->path, now()->addMinutes(60));
                } catch (Throwable) {
                    return $storage->url($this->path);
                }
            },
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * @return HasMany<CuratorMediaUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CuratorMediaUsage::class, 'curator_media_id');
    }

    public function isInUse(): bool
    {
        return $this->usages()->exists();
    }

    public function getUsageCount(): int
    {
        return $this->usages()->count();
    }

    public function getDeletionBlockedMessage(): string
    {
        return trans('Media ini sedang dipakai di :count lokasi dan tidak bisa dihapus.', [
            'count' => $this->getUsageCount(),
        ]);
    }

    /**
     * @return Attribute<string, never>
     */
    public function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->url,
        );
    }

    /**
     * @return Attribute<string, never>
     */
    public function mediumUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->url,
        );
    }

    /**
     * @return Attribute<string, never>
     */
    public function largeUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->url,
        );
    }

    protected static function booted(): void
    {
        // Apply Global Scope for ownership-based filtering.
        self::addGlobalScope(new CuratorMediaScope);

        self::creating(function (self $media): void {
            if (empty($media->created_by) && Auth::check()) {
                $media->created_by = (string) Auth::id();
            }

            if (empty($media->privacy)) {
                $media->privacy = Privacy::PRIVATE;
            }
        });

        self::saving(function (self $media): void {
            // Priority 1: If privacy is changed, visibility must follow.
            if ($media->isDirty('privacy')) {
                $media->visibility = $media->privacy === Privacy::PUBLIC ? 'public' : 'private';
            }
            // Priority 2: If visibility is changed, privacy must follow (to stay in sync).
            elseif ($media->isDirty('visibility')) {
                $media->privacy = $media->visibility === 'public' ? Privacy::PUBLIC : Privacy::PRIVATE;
            }
            // Priority 3: Always ensure they match based on privacy source of truth.
            else {
                $media->visibility = $media->privacy === Privacy::PUBLIC ? 'public' : 'private';
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'privacy' => Privacy::class,
            'curations' => 'array',
            'exif' => 'array',
        ];
    }
}
