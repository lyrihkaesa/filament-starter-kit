<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Privacy;
use Awcodes\Curator\Models\Media;
use Database\Factories\CuratorMediaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class CuratorMedia extends Media
{
    /** @use HasFactory<CuratorMediaFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
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
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
        self::creating(function (self $media): void {
            if (empty($media->{$media->getKeyName()})) {
                $media->{$media->getKeyName()} = (string) Str::uuid();
            }

            if (empty($media->created_by) && Auth::check()) {
                $media->created_by = (string) Auth::id();
            }

            if (empty($media->privacy)) {
                $media->privacy = Privacy::PRIVATE->value;
            }
        });

        self::saving(function (self $media): void {
            // Sync physical visibility with logical privacy
            $media->visibility = $media->privacy === Privacy::PUBLIC ? 'public' : 'private';
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
