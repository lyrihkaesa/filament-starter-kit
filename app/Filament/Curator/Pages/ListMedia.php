<?php

declare(strict_types=1);

namespace App\Filament\Curator\Pages;

use App\Filament\Curator\MediaResource;
use Awcodes\Curator\Resources\Media\Pages\ListMedia as BaseListMedia;

final class ListMedia extends BaseListMedia
{
    protected static string $resource = MediaResource::class;
}
