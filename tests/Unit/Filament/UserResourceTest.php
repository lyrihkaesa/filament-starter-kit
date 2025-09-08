<?php

declare(strict_types=1);

use App\Filament\Resources\Users\UserResource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;

uses(TestCase::class);

it('configures UserResource form, infolist, table, relations, pages, and query', function () {
    // Mock Schema for form()
    $schemaForm = $this->getMockBuilder(Schema::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['components'])
        ->getMock();
    $schemaForm->method('components')->willReturnSelf();

    expect(UserResource::form($schemaForm))->toBe($schemaForm);

    // Mock Schema for infolist()
    $schemaInfo = $this->getMockBuilder(Schema::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['components'])
        ->getMock();
    $schemaInfo->method('components')->willReturnSelf();

    expect(UserResource::infolist($schemaInfo))->toBe($schemaInfo);

    // Mock Table for table()
    $table = $this->getMockBuilder(Table::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['columns', 'filters', 'recordActions', 'toolbarActions'])
        ->getMock();
    $table->method('columns')->willReturnSelf();
    $table->method('filters')->willReturnSelf();
    $table->method('recordActions')->willReturnSelf();
    $table->method('toolbarActions')->willReturnSelf();

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
