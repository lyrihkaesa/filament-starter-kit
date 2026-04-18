<?php

declare(strict_types=1);

use App\Filament\Resources\Activities\Pages\ManageActivities;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

    // Setup roles and permissions
    $adminRole = Role::findOrCreate('admin', 'web');
    $memberRole = Role::findOrCreate('member', 'web');

    $viewAnyPermission = Permission::findOrCreate('ViewAny:Activity', 'web');
    $viewPermission = Permission::findOrCreate('View:Activity', 'web');

    $adminRole->givePermissionTo([$viewAnyPermission, $viewPermission]);
    $memberRole->givePermissionTo([$viewAnyPermission, $viewPermission]);
});

it('allows admin to see all activities', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    activity()->causedBy($user1)->log('Activity 1');
    activity()->causedBy($user2)->log('Activity 2');

    $this->actingAs($admin);

    livewire(ManageActivities::class)
        ->assertSuccessful()
        ->assertSee($user1->name)
        ->assertSee($user2->name);
});

it('restricts members to see only their own activities', function (): void {
    $member1 = User::factory()->create();
    $member1->name = 'Member One';
    $member1->save();
    $member1->assignRole('member');

    $member2 = User::factory()->create();
    $member2->name = 'Member Two';
    $member2->save();
    $member2->assignRole('member');

    activity()->causedBy($member1)->log('Activity for 1');
    activity()->causedBy($member2)->log('Activity for 2');

    $this->actingAs($member1);

    livewire(ManageActivities::class)
        ->assertSuccessful()
        ->assertSee('Member One')
        ->assertDontSee('Member Two');
});

it('can filter activities by subject type and subject id via URL', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $targetUser = User::factory()->create();
    $targetUser->name = 'Target User';
    $targetUser->save();

    $otherUser = User::factory()->create();
    $otherUser->name = 'Other User';
    $otherUser->save();

    // Log activities where they are the SUBJECT
    activity()->performedOn($targetUser)->log('Target Subject Log');
    activity()->performedOn($otherUser)->log('Other Subject Log');

    $this->actingAs($admin);

    // Apply filters explicitly
    livewire(ManageActivities::class)
        ->filterTable('subject_type', 'user')
        ->filterTable('subject_id', ['value' => (string) $targetUser->id])
        ->assertSuccessful()
        ->assertSee('Target Subject Log')
        ->assertDontSee('Other Subject Log');
});
