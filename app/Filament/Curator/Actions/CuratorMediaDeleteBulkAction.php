<?php

declare(strict_types=1);

namespace App\Filament\Curator\Actions;

use App\Models\CuratorMedia;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

final readonly class CuratorMediaDeleteBulkAction
{
    public static function make(): DeleteBulkAction
    {
        return DeleteBulkAction::make()
            ->authorize(fn (): bool => self::canDeleteAny())
            ->before(function (DeleteBulkAction $deleteBulkAction, EloquentCollection|Collection|LazyCollection $records): void {
                $blockedCount = $records
                    ->filter(function (mixed $record): bool {
                        if (! $record instanceof CuratorMedia) {
                            return false;
                        }

                        return $record->isInUse() && ! self::canDeleteUsed();
                    })
                    ->count();

                if ($blockedCount === 0) {
                    return;
                }

                Notification::make()
                    ->danger()
                    ->title(__('Delete dibatalkan'))
                    ->body(trans(':count media masih dipakai dan tidak bisa dihapus.', [
                        'count' => $blockedCount,
                    ]))
                    ->send();

                $deleteBulkAction->cancel();
            });
    }

    private static function canDeleteAny(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->can('Delete:CuratorMedia')) {
            return true;
        }

        return $user->can('DeleteOwn:CuratorMedia');
    }

    private static function canDeleteUsed(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->can('DeleteUsed:CuratorMedia');
    }
}
