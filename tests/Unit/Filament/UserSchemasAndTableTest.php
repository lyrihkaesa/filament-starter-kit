<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Tests\TestCase;

uses(TestCase::class);

it('configures UserForm schema', function () {
    $schema = $this->getMockBuilder(Schema::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['components'])
        ->getMock();
    $schema->method('components')->willReturnSelf();

    expect(UserForm::configure($schema))->toBe($schema);
});

it('configures UserInfolist schema', function () {
    $schema = $this->getMockBuilder(Schema::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['components'])
        ->getMock();
    $schema->method('components')->willReturnSelf();

    expect(UserInfolist::configure($schema))->toBe($schema);
});

it('configures UsersTable', function () {
    $table = $this->getMockBuilder(Table::class)
        ->disableOriginalConstructor()
        ->onlyMethods(['columns', 'filters', 'recordActions', 'toolbarActions'])
        ->getMock();

    $table->method('columns')->willReturnSelf();
    $table->method('filters')->willReturnSelf();
    $table->method('recordActions')->willReturnSelf();
    $table->method('toolbarActions')->willReturnSelf();

    expect(UsersTable::configure($table))->toBe($table);
});
