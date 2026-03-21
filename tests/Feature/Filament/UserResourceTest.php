<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can list users', function () {
    User::factory()->count(10)->create();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords(User::limit(10)->get());
});

it('can create users using action class', function () {
    Livewire::test(CreateUser::class)
        ->set('data.name', 'New User')
        ->set('data.email', 'new@example.com')
        ->set('data.password', 'password')
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'new@example.com',
    ]);
});

it('can update users using action class', function () {
    $user = User::factory()->create();
    $updatedName = 'Updated Name';

    Livewire::test(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->set('data.name', $updatedName)
        ->set('data.password', 'password')
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh()->name)->toBe($updatedName);
});

it('can delete users using action class from table', function () {
    $user = User::factory()->create();

    Livewire::test(ListUsers::class)
        ->callTableAction(DeleteAction::class, $user);

    $this->assertSoftDeleted($user);
});

it('can delete users using action class from edit page', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertSoftDeleted($user);
});
