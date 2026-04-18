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
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('Content Management');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'alt', 'title', 'caption'];
    }

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
