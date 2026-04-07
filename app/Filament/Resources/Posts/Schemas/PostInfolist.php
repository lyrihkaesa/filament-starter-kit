<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->components([
                        ImageEntry::make('thumbnailCurator.url')
                            ->circular(),
                        TextEntry::make('title'),
                        TextEntry::make('slug'),
                        TextEntry::make('author.name'),
                        IconEntry::make('published_at')
                            ->label('Published')
                            ->boolean(),
                        TextEntry::make('published_at')
                            ->label('Published At')
                            ->dateTime()
                            ->placeholder('Draft'),
                        TextEntry::make('content')
                            ->columnSpanFull()
                            ->html(),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
