<?php

declare(strict_types=1);

namespace App\Models;

use Awcodes\Curator\Models\Media;
use Database\Factories\CuratorMediaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class CuratorMedia extends Media
{
    /** @use HasFactory<CuratorMediaFactory> */
    use HasFactory;

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
}
