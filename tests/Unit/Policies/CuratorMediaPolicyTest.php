<?php

declare(strict_types=1);

use App\Enums\Privacy;
use App\Models\CuratorMedia;
use App\Models\CuratorMediaUsage;
use App\Models\Permission;
use App\Models\User;
use App\Policies\CuratorMediaPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach ([
        'ViewAny:CuratorMedia',
        'View:CuratorMedia',
        'ViewOwn:CuratorMedia',
        'Create:CuratorMedia',
        'Update:CuratorMedia',
        'UpdateOwn:CuratorMedia',
        'Delete:CuratorMedia',
        'DeleteOwn:CuratorMedia',
        'DeleteUsed:CuratorMedia',
        'Restore:CuratorMedia',
        'RestoreOwn:CuratorMedia',
        'ForceDelete:CuratorMedia',
        'ForceDeleteOwn:CuratorMedia',
        'ForceDeleteUsed:CuratorMedia',
        'Replicate:CuratorMedia',
        'Reorder:CuratorMedia',
    ] as $permission) {
        Permission::findOrCreate($permission);
    }

    $this->policy = new CuratorMediaPolicy();
});

it('checks view any permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();

    $user->givePermissionTo('ViewAny:CuratorMedia');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('applies view rules based on privacy and ownership', function (): void {
    $guestVisibleMedia = CuratorMedia::factory()->create([
        'privacy' => Privacy::PUBLIC,
    ]);

    expect($this->policy->view(null, $guestVisibleMedia))->toBeTrue();

    $memberMedia = CuratorMedia::factory()->create([
        'privacy' => Privacy::MEMBER,
    ]);

    $authenticatedUser = User::factory()->create();
    expect($this->policy->view($authenticatedUser, $memberMedia))->toBeTrue()
        ->and($this->policy->view(null, $memberMedia))->toBeFalse();

    $owner = User::factory()->create();
    $owner->givePermissionTo('ViewOwn:CuratorMedia');

    $admin = User::factory()->create();
    $admin->givePermissionTo('View:CuratorMedia');

    $other = User::factory()->create();

    $privateMedia = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
        'privacy' => Privacy::PRIVATE,
    ]);

    expect($this->policy->view($other, $privateMedia))->toBeFalse()
        ->and($this->policy->view($owner, $privateMedia))->toBeTrue()
        ->and($this->policy->view($admin, $privateMedia))->toBeTrue();
});

it('checks create permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();

    $user->givePermissionTo('Create:CuratorMedia');

    expect($this->policy->create($user))->toBeTrue();
});

it('authorizes update for admins and owners only', function (): void {
    $owner = User::factory()->create();
    $owner->givePermissionTo('UpdateOwn:CuratorMedia');

    $admin = User::factory()->create();
    $admin->givePermissionTo('Update:CuratorMedia');

    $other = User::factory()->create();
    $other->givePermissionTo('UpdateOwn:CuratorMedia');

    $media = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
    ]);

    expect($this->policy->update($admin, $media))->toBeTrue()
        ->and($this->policy->update($owner, $media))->toBeTrue()
        ->and($this->policy->update($other, $media))->toBeFalse();
});

it('blocks delete when media is still in use', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('DeleteOwn:CuratorMedia');

    $media = CuratorMedia::factory()->create([
        'created_by' => $user->id,
    ]);

    CuratorMediaUsage::query()->create([
        'curator_media_id' => (string) $media->id,
        'model_id' => (string) $user->id,
        'model_type' => $user->getMorphClass(),
        'field_name' => 'avatar_curator_id',
    ]);

    expect($this->policy->delete($user, $media))->toBeFalse();
});

it('allows deleting used media for admin or special permission', function (): void {
    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:CuratorMedia');

    $ownerWithOverride = User::factory()->create();
    $ownerWithOverride->givePermissionTo(['DeleteOwn:CuratorMedia', 'DeleteUsed:CuratorMedia']);

    $adminMedia = CuratorMedia::factory()->create([
        'created_by' => $admin->id,
    ]);
    $ownerMedia = CuratorMedia::factory()->create([
        'created_by' => $ownerWithOverride->id,
    ]);

    CuratorMediaUsage::query()->create([
        'curator_media_id' => (string) $adminMedia->id,
        'model_id' => (string) $admin->id,
        'model_type' => $admin->getMorphClass(),
        'field_name' => 'avatar_curator_id',
    ]);

    CuratorMediaUsage::query()->create([
        'curator_media_id' => (string) $ownerMedia->id,
        'model_id' => (string) $ownerWithOverride->id,
        'model_type' => $ownerWithOverride->getMorphClass(),
        'field_name' => 'avatar_curator_id',
    ]);

    expect($this->policy->delete($admin, $adminMedia))->toBeTrue()
        ->and($this->policy->delete($ownerWithOverride, $ownerMedia))->toBeTrue();
});

it('authorizes delete for admins and owners when media is unused', function (): void {
    $owner = User::factory()->create();
    $owner->givePermissionTo('DeleteOwn:CuratorMedia');

    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:CuratorMedia');

    $other = User::factory()->create();
    $other->givePermissionTo('DeleteOwn:CuratorMedia');

    $media = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
    ]);

    expect($this->policy->delete($admin, $media))->toBeTrue()
        ->and($this->policy->delete($owner, $media))->toBeTrue()
        ->and($this->policy->delete($other, $media))->toBeFalse();
});

it('authorizes restore for admins and owners only', function (): void {
    $owner = User::factory()->create();
    $owner->givePermissionTo('RestoreOwn:CuratorMedia');

    $admin = User::factory()->create();
    $admin->givePermissionTo('Restore:CuratorMedia');

    $other = User::factory()->create();
    $other->givePermissionTo('RestoreOwn:CuratorMedia');

    $media = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
    ]);

    expect($this->policy->restore($admin, $media))->toBeTrue()
        ->and($this->policy->restore($owner, $media))->toBeTrue()
        ->and($this->policy->restore($other, $media))->toBeFalse();
});

it('blocks force delete when media is still in use', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('ForceDeleteOwn:CuratorMedia');

    $media = CuratorMedia::factory()->create([
        'created_by' => $user->id,
    ]);

    CuratorMediaUsage::query()->create([
        'curator_media_id' => (string) $media->id,
        'model_id' => (string) $user->id,
        'model_type' => $user->getMorphClass(),
        'field_name' => 'avatar_curator_id',
    ]);

    expect($this->policy->forceDelete($user, $media))->toBeFalse();
});

it('allows force deleting used media for admin or special permission', function (): void {
    $admin = User::factory()->create();
    $admin->givePermissionTo('ForceDelete:CuratorMedia');

    $ownerWithOverride = User::factory()->create();
    $ownerWithOverride->givePermissionTo(['ForceDeleteOwn:CuratorMedia', 'ForceDeleteUsed:CuratorMedia']);

    $adminMedia = CuratorMedia::factory()->create([
        'created_by' => $admin->id,
    ]);
    $ownerMedia = CuratorMedia::factory()->create([
        'created_by' => $ownerWithOverride->id,
    ]);

    CuratorMediaUsage::query()->create([
        'curator_media_id' => (string) $adminMedia->id,
        'model_id' => (string) $admin->id,
        'model_type' => $admin->getMorphClass(),
        'field_name' => 'avatar_curator_id',
    ]);

    CuratorMediaUsage::query()->create([
        'curator_media_id' => (string) $ownerMedia->id,
        'model_id' => (string) $ownerWithOverride->id,
        'model_type' => $ownerWithOverride->getMorphClass(),
        'field_name' => 'avatar_curator_id',
    ]);

    expect($this->policy->forceDelete($admin, $adminMedia))->toBeTrue()
        ->and($this->policy->forceDelete($ownerWithOverride, $ownerMedia))->toBeTrue();
});

it('authorizes force delete for admins and owners when media is unused', function (): void {
    $owner = User::factory()->create();
    $owner->givePermissionTo('ForceDeleteOwn:CuratorMedia');

    $admin = User::factory()->create();
    $admin->givePermissionTo('ForceDelete:CuratorMedia');

    $other = User::factory()->create();
    $other->givePermissionTo('ForceDeleteOwn:CuratorMedia');

    $media = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
    ]);

    expect($this->policy->forceDelete($admin, $media))->toBeTrue()
        ->and($this->policy->forceDelete($owner, $media))->toBeTrue()
        ->and($this->policy->forceDelete($other, $media))->toBeFalse();
});

it('checks replicate permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->replicate($user))->toBeFalse();

    $user->givePermissionTo('Replicate:CuratorMedia');

    expect($this->policy->replicate($user))->toBeTrue();
});

it('checks reorder permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->reorder($user))->toBeFalse();

    $user->givePermissionTo('Reorder:CuratorMedia');

    expect($this->policy->reorder($user))->toBeTrue();
});
