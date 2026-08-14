<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use Tests\TestCase;

pest()->extend(TestCase::class);

/**
 * Helper to invoke protected getHeaderActions() without booting Livewire.
 */
function invokeHeaderActions(string $class): array
{
    $ref = new ReflectionClass($class);
    $instance = $ref->newInstanceWithoutConstructor();
    $method = $ref->getMethod('getHeaderActions');

    return $method->invoke($instance);
}

it('list users page defines header actions', function (): void {
    $actions = invokeHeaderActions(ListUsers::class);
    expect($actions)->toBeArray()->not->toBeEmpty();
});

it('edit user page defines header actions', function (): void {
    $actions = invokeHeaderActions(EditUser::class);
    expect($actions)->toBeArray()->not->toBeEmpty();
});

it('view user page defines header actions', function (): void {
    $actions = invokeHeaderActions(ViewUser::class);
    expect($actions)->toBeArray()->not->toBeEmpty();
});
