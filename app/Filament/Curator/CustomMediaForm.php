<?php

declare(strict_types=1);

namespace App\Filament\Curator;

use App\Enums\ViewVisibility;
use Awcodes\Curator\Resources\Media\Schemas\MediaForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

final class CustomMediaForm extends MediaForm
{
    /**
     * @return array<int, Grid>
     */
    public static function getSchema(): array
    {
        return [
            Grid::make(2)
                ->components([
                    Section::make()
                        ->components([
                            TextInput::make('name')
                                ->label(__('curator::forms.fields.name'))
                                ->required()
                                ->columnSpan('full'),
                            TextInput::make('alt')
                                ->label(__('curator::forms.fields.alt'))
                                ->hint(__('curator::forms.fields.alt_hint'))
                                ->columnSpan('full'),
                            TextInput::make('title')
                                ->label(__('curator::forms.fields.title'))
                                ->columnSpan('full'),
                            Textarea::make('caption')
                                ->label(__('curator::forms.fields.caption'))
                                ->rows(2)
                                ->columnSpan('full'),
                            Textarea::make('description')
                                ->label(__('curator::forms.fields.description'))
                                ->rows(2)
                                ->columnSpan('full'),
                            Select::make('view_visibility')
                                ->label('Visibility')
                                ->options(ViewVisibility::class)
                                ->default(ViewVisibility::PUBLIC)
                                ->required()
                                ->columnSpan('full'),
                        ])
                        ->columnSpan(1),
                    Section::make()
                        ->components([
                            ViewField::make('preview')
                                ->view('curator::components.forms.preview')
                                ->columnSpan('full'),
                        ])
                        ->columnSpan(1),
                ]),
        ];
    }
}
