<?php

declare(strict_types=1);

namespace App\Filament\Curator;

use App\Filament\Curator\Pages\CreateMedia;
use App\Filament\Curator\Pages\ListMedia;
use App\Filament\Pages\Media\EditMedia;
use Awcodes\Curator\Resources\Media\MediaResource as BaseMediaResource;
use Filament\Tables\Table;

final class MediaResource extends BaseMediaResource
{
    public static function table(Table $table): Table
    {
        return MediaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'edit' => EditMedia::route('/{record}/edit'),
        ];
    }
}
