<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Tests\TestCase;

uses(TestCase::class);

it('configures UserForm schema', function (): void {
    $schema = mock(Schema::class);
    $schema->shouldReceive('components')->once()->andReturnSelf();

    expect(UserForm::configure($schema))->toBe($schema);
});

it('configures UserInfolist schema', function (): void {
    $schema = mock(Schema::class);
    $schema->shouldReceive('components')->once()->andReturnSelf();

    expect(UserInfolist::configure($schema))->toBe($schema);
});

it('configures UsersTable', function (): void {
    $table = mock(Table::class);
    $table->shouldReceive('modifyQueryUsing')->once()->andReturnSelf();
    $table->shouldReceive('columns')->once()->andReturnSelf();
    $table->shouldReceive('filters')->once()->andReturnSelf();
    $table->shouldReceive('recordActions')->once()->andReturnSelf();
    $table->shouldReceive('toolbarActions')->once()->andReturnSelf();

    expect(UsersTable::configure($table))->toBe($table);
});
