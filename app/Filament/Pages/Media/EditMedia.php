<?php

declare(strict_types=1);

namespace App\Filament\Pages\Media;

use App\Filament\Curator\Actions\CuratorMediaDeleteAction;
use App\Filament\Curator\Actions\CuratorMediaUsagesAction;
use App\Filament\Curator\MediaResource;
use App\Models\CuratorMedia;
use App\Models\User;
use Awcodes\Curator\Resources\Media\Pages\EditMedia as BaseEditMedia;
use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

// @codeCoverageIgnoreStart
final class EditMedia extends BaseEditMedia
{
    protected static string $resource = MediaResource::class;

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
                ->url(
                    fn (): ?string => $this->record instanceof CuratorMedia ? $this->record->url : null,
                    shouldOpenInNewTab: true
                )
                ->label(trans('curator::views.panel.view')),
            CuratorMediaUsagesAction::make(),
            CuratorMediaDeleteAction::make()
                ->modalHeading(fn (): string => __('Delete Media')),
        ];
    }

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->record instanceof CuratorMedia && $this->record->isInUse() && ! $this->canDeleteUsedMedia()) {
            return $this->record->getDeletionBlockedMessage();
        }

        return parent::getSubheading();
    }

    private function canDeleteUsedMedia(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->can('DeleteUsed:CuratorMedia');
    }
}
// @codeCoverageIgnoreEnd

