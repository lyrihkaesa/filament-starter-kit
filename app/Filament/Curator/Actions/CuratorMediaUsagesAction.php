<?php

declare(strict_types=1);

namespace App\Filament\Curator\Actions;

use App\Actions\Media\ListCuratorMediaUsagesAction;
use App\Models\CuratorMedia;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;

final class CuratorMediaUsagesAction
{
    public static function make(): Action
    {
        return Action::make('viewUsages')
            ->label(__('Dipakai Di Mana'))
            ->color('gray')
            ->icon('heroicon-o-link')
            ->badge(fn (CuratorMedia $record): string => (string) $record->getUsageCount())
            ->tooltip(fn (CuratorMedia $record): string => $record->isInUse() ? __('Lihat lokasi penggunaan media ini') : __('Media ini belum dipakai'))
            ->modalHeading(__('Lokasi Penggunaan Media'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('Tutup'))
            ->modalWidth('4xl')
            ->modalDescription(new HtmlString(__('Daftar model dan field yang masih mereferensikan media ini.')))
            ->modalContent(fn (CuratorMedia $record, ListCuratorMediaUsagesAction $listCuratorMediaUsagesAction): View => view(
                'filament.media.used-by-modal',
                ['usages' => $listCuratorMediaUsagesAction->handle($record)],
            ));
    }
}
