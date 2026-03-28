<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->components([
                        Section::make()
                            ->columnSpan(1)
                            ->components([
                                TextInput::make('title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('slug', Str::slug($state ?? ''))),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Textarea::make('content')
                                    ->required()
                                    ->rows(10),
                            ]),
                        Section::make()
                            ->columnSpan(1)
                            ->components([
                                CuratorPicker::make('thumbnail_curator_id')
                                    ->label(__('Thumbnail'))
                                    ->relationship('thumbnailCurator', 'id')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(3072)
                                    ->disk('public')
                                    ->directory('posts/thumbnails')
                                    ->visibility('public'),
                                Toggle::make('is_published')
                                    ->required()
                                    ->default(false),
                                Select::make('author_id')
                                    ->relationship('author', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->default(auth()->id()),
                            ]),
                    ]),
            ]);
    }
}
