<?php

declare(strict_types=1);

namespace App\Filament\Curator;

use App\Enums\Privacy;
use App\Models\CuratorMedia;
use App\Models\User;
use Awcodes\Curator\Resources\Media\Schemas\MediaForm;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

// @codeCoverageIgnoreStart
final class CustomMediaForm extends MediaForm
{
    /**
     * @return array<int, Component|Entry>
     */
    public static function getAdditionalInformationFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(trans('curator::forms.fields.name'))
                ->hiddenOn('create')
                ->required()
                ->dehydrateStateUsing(function (mixed $component, ?string $state): string {
                    $slugged = Str::slug($state ?? '');
                    if (is_object($component) && method_exists($component, 'state')) {
                        $component->state($slugged);
                    }

                    return $slugged;
                }),
            TextInput::make('alt')
                ->label(trans('curator::forms.fields.alt'))
                ->hint(fn (): HtmlString => new HtmlString('<a href="https://www.w3.org/WAI/tutorials/images/decision-tree" class="filament-link text-primary-500 text-xs" target="_blank">'.trans('curator::forms.fields.alt_hint').'</a>')),
            TextInput::make('title')
                ->label(trans('curator::forms.fields.title')),
            Textarea::make('caption')
                ->label(trans('curator::forms.fields.caption'))
                ->rows(2),
            Textarea::make('description')
                ->label(trans('curator::forms.fields.description'))
                ->rows(2),
            ToggleButtons::make('privacy')
                ->label(__('Privacy'))
                ->options(Privacy::class)
                ->default(Privacy::PUBLIC)
                ->inline()
                ->required(),
            TextEntry::make('created_by')
                ->label(__('Created By'))
                ->state(function (CuratorMedia $record): string {
                    /** @var User|null $creator */
                    $creator = $record->creator;

                    return $creator->name ?? __('System');
                }),
        ];
    }
}

// @codeCoverageIgnoreEnd
