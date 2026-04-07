<?php

declare(strict_types=1);

namespace App\Filament\Curator;

use App\Filament\Curator\Actions\CuratorMediaDeleteAction;
use App\Filament\Curator\Actions\CuratorMediaDeleteBulkAction;
use Awcodes\Curator\Resources\Media\Tables\MediaTable as BaseMediaTable;
use Exception;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class MediaTable extends BaseMediaTable
{
    /**
     * @throws Exception
     */
    public static function configure(Table $table): Table
    {
        return parent::configure($table)
            ->recordActions([
                EditAction::make(),
                CuratorMediaDeleteAction::make(),
            ])
            ->toolbarActions([
                CuratorMediaDeleteBulkAction::make(),
            ]);
    }

    /**
     * @return array<int, mixed>
     *
     * @throws Exception
     */
    public static function getDefaultTableColumns(): array
    {
        $columns = parent::getDefaultTableColumns();

        array_splice($columns, 4, 0, [
            TextColumn::make('usages_count')
                ->label(__('Dipakai'))
                ->counts('usages')
                ->badge()
                ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),
        ]);

        return $columns;
    }
}
