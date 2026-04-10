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
            ->disabled(fn (CuratorMedia $record): bool => $record->isInUse() && ! self::canDeleteUsed($record))
            ->tooltip(fn (CuratorMedia $record): ?string => ($record->isInUse() && ! self::canDeleteUsed($record)) ? $record->getDeletionBlockedMessage() : null)
            ->modalDescription(fn (CuratorMedia $record): string => ($record->isInUse() && ! self::canDeleteUsed($record))
                ? $record->getDeletionBlockedMessage()
                : __('Are you sure you want to delete this media?'))
            ->using(fn (CuratorMedia $record, DeleteCuratorMediaRecordAction $deleteMediaAction): bool => $deleteMediaAction->handle(
                media: $record,
                deleterId: auth()->id(),
                allowDeleteWhenUsed: self::canDeleteUsed($record),
            ));
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

    private static function canDeleteUsed(CuratorMedia $record): bool
    {
        if (! self::canDelete($record)) {
            return false;
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->can('Delete:CuratorMedia') || $user->can('DeleteUsed:CuratorMedia');
    }
}
