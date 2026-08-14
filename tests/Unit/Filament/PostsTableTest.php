<?php

declare(strict_types=1);

use App\Filament\Resources\Posts\Tables\PostsTable;
use Filament\Tables\Table;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('configures PostsTable', function (): void {
    $table = mock(Table::class);
    $table->shouldReceive('modifyQueryUsing')->once()->andReturnSelf();
    $table->shouldReceive('columns')->once()->andReturnSelf();
    $table->shouldReceive('filters')->once()->andReturnSelf();
    $table->shouldReceive('recordActions')->once()->andReturnSelf();
    $table->shouldReceive('toolbarActions')->once()->andReturnSelf();

    expect(PostsTable::configure($table))->toBe($table);
});
