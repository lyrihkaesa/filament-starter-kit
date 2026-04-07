<?php

declare(strict_types=1);

namespace App\Filament\Pages\Media;

use App\Filament\Curator\Actions\CuratorMediaDeleteAction;
use Awcodes\Curator\Resources\Media\Pages\EditMedia as BaseEditMedia;
use Filament\Actions\Action;

final class EditMedia extends BaseEditMedia
{
    public function getSubheading(): ?string
    {
        return $this->record->isInUse()
            ? $this->record->getDeletionBlockedMessage()
            : null;
    }

    /**
     * @return array<int, Action>
     */
    public function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->action('save')
                ->label(trans('curator::views.panel.edit_save')),
            Action::make('preview')
                ->color('gray')
                ->url($this->record->url, shouldOpenInNewTab: true)
                ->label(trans('curator::views.panel.view')),
            CuratorMediaDeleteAction::make()
                ->modalHeading(fn (): string => __('Delete Media')),
        ];
    }
}
