<?php

declare(strict_types=1);

namespace App\Filament\Curator\Pages;

use App\Filament\Curator\MediaResource;
use Awcodes\Curator\Resources\Media\Pages\CreateMedia as BaseCreateMedia;

final class CreateMedia extends BaseCreateMedia
{
    protected static string $resource = MediaResource::class;
}
