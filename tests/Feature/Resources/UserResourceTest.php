<?php

declare(strict_types=1);

namespace Tests\Feature\Resources;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::findOrCreate('super_admin');
});

it('can filter active users', function (): void {
    $activeUser = User::factory()->create();
    $activeUser->assignRole('super_admin');

    $deletedUser = User::factory()->create(['deleted_at' => now()]);
    $anonymizedUser = User::factory()->create(['anonymized_at' => now(), 'deleted_at' => now()]);

    Livewire::actingAs($activeUser)
        ->test(ListUsers::class)
        ->filterTable('status', 'active')
        ->assertCanSeeTableRecords([$activeUser])
        ->assertCanNotSeeTableRecords([$deletedUser, $anonymizedUser]);
});

it('can filter deleted users', function (): void {
    $activeUser = User::factory()->create();
    $activeUser->assignRole('super_admin');

    $deletedUser = User::factory()->create(['deleted_at' => now()]);
    $anonymizedUser = User::factory()->create(['anonymized_at' => now(), 'deleted_at' => now()]);

    Livewire::actingAs($activeUser)
        ->test(ListUsers::class)
        ->filterTable('status', 'deleted')
        ->assertCanSeeTableRecords([$deletedUser])
        ->assertCanNotSeeTableRecords([$activeUser, $anonymizedUser]);
});

it('can filter anonymized users', function (): void {
    $activeUser = User::factory()->create();
    $activeUser->assignRole('super_admin');

    $deletedUser = User::factory()->create(['deleted_at' => now()]);
    $anonymizedUser = User::factory()->create(['anonymized_at' => now(), 'deleted_at' => now()]);

    Livewire::actingAs($activeUser)
        ->test(ListUsers::class)
        ->filterTable('status', 'anonymized')
        ->assertCanSeeTableRecords([$anonymizedUser])
        ->assertCanNotSeeTableRecords([$activeUser, $deletedUser]);
});

it('shows only active users by default', function (): void {
    $activeUser = User::factory()->create();
    $activeUser->assignRole('super_admin');

    $deletedUser = User::factory()->create(['deleted_at' => now()]);
    $anonymizedUser = User::factory()->create(['anonymized_at' => now(), 'deleted_at' => now()]);

    Livewire::actingAs($activeUser)
        ->test(ListUsers::class)
        ->assertCanSeeTableRecords([$activeUser])
        ->assertCanNotSeeTableRecords([$deletedUser, $anonymizedUser]);
});
