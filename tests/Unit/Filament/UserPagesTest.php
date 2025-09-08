<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use Tests\TestCase;

uses(TestCase::class);

/**
 * Helper to invoke protected getHeaderActions() without booting Livewire.
 */
function invokeHeaderActions(string $class): array
{
    $ref = new ReflectionClass($class);
    $instance = $ref->newInstanceWithoutConstructor();
    $method = $ref->getMethod('getHeaderActions');
    $method->setAccessible(true);

    return $method->invoke($instance);
}

it('list users page defines header actions', function () {
    $actions = invokeHeaderActions(ListUsers::class);
    expect($actions)->toBeArray()->not->toBeEmpty();
});

it('edit user page defines header actions', function () {
    $actions = invokeHeaderActions(EditUser::class);
    expect($actions)->toBeArray()->not->toBeEmpty();
});

it('view user page defines header actions', function () {
    $actions = invokeHeaderActions(ViewUser::class);
    expect($actions)->toBeArray()->not->toBeEmpty();
});
