<?php

declare(strict_types=1);

namespace App\Filament\Curator\Actions;

use App\Actions\Media\DeleteCuratorMediaAction as DeleteCuratorMediaRecordAction;
use App\Models\CuratorMedia;
use App\Models\User;
use Filament\Actions\DeleteAction;

final class CuratorMediaDeleteAction
{
    public static function make(): DeleteAction
    {
        return DeleteAction::make()
            ->authorize(fn (CuratorMedia $record): bool => self::canDelete($record))
            ->disabled(fn (CuratorMedia $record): bool => $record->isInUse())
            ->tooltip(fn (CuratorMedia $record): ?string => $record->isInUse() ? $record->getDeletionBlockedMessage() : null)
            ->modalDescription(fn (CuratorMedia $record): string => $record->isInUse()
                ? $record->getDeletionBlockedMessage()
                : __('Are you sure you want to delete this media?'))
            ->using(fn (CuratorMedia $record, DeleteCuratorMediaRecordAction $deleteMediaAction): bool => $deleteMediaAction->handle($record));
    }

    private static function canDelete(CuratorMedia $record): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->can('Delete:CuratorMedia')) {
            return true;
        }

        return $user->id === $record->created_by && $user->can('DeleteOwn:CuratorMedia');
    }
}
