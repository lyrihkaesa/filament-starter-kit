<?php

declare(strict_types=1);

use App\Filament\Resources\Users\UserResource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('configures UserResource form, infolist, table, relations, pages, and query', function (): void {
    // Mock Schema for form()
    $schemaForm = mock(Schema::class);
    $schemaForm->shouldReceive('components')->once()->andReturnSelf();

    expect(UserResource::form($schemaForm))->toBe($schemaForm);

    // Mock Schema for infolist()
    $schemaInfo = mock(Schema::class);
    $schemaInfo->shouldReceive('components')->once()->andReturnSelf();

    expect(UserResource::infolist($schemaInfo))->toBe($schemaInfo);

    // Mock Table for table()
    $table = mock(Table::class);
    $table->shouldReceive('modifyQueryUsing')->once()->andReturnSelf();
    $table->shouldReceive('columns')->once()->andReturnSelf();
    $table->shouldReceive('filters')->once()->andReturnSelf();
    $table->shouldReceive('recordActions')->once()->andReturnSelf();
    $table->shouldReceive('toolbarActions')->once()->andReturnSelf();

    expect(UserResource::table($table))->toBe($table);

    // getRelations()
    expect(UserResource::getRelations())->toBeArray()->toBeEmpty();

    // getPages()
    $pages = UserResource::getPages();
    expect($pages)->toBeArray()
        ->toHaveKeys(['index', 'create', 'view', 'edit']);

    // getRecordRouteBindingEloquentQuery()
    $query = UserResource::getRecordRouteBindingEloquentQuery();
    expect($query)->toBeInstanceOf(Builder::class);
});
