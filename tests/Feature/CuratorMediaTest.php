<?php

declare(strict_types=1);

use App\Enums\Privacy;
use App\Models\CuratorMedia;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->adminRole = Role::create(['name' => 'admin']);
    $this->superAdminRole = Role::create(['name' => 'super_admin']);
});

it('sets created_by and privacy on creation', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $media = CuratorMedia::factory()->create([
        'created_by' => null,
    ]);

    expect($media->created_by)->toBe($user->id)
        ->and($media->privacy)->toBe(Privacy::PRIVATE);
});

it('has a creator relationship', function (): void {
    $user = User::factory()->create();
    $media = CuratorMedia::factory()->create(['created_by' => $user->id]);

    expect($media->creator)->toBeInstanceOf(User::class)
        ->and($media->creator->id)->toBe($user->id);
});

it('allows everyone to view public media', function (): void {
    $media = CuratorMedia::factory()->create(['privacy' => Privacy::PUBLIC]);

    expect(Gate::allows('view', $media))->toBeTrue();

    $this->actingAs(User::factory()->create());
    expect(Gate::allows('view', $media))->toBeTrue();
});

it('allows only logged in users to view member media', function (): void {
    $media = CuratorMedia::factory()->create(['privacy' => Privacy::MEMBER]);

    // Guest
    expect(Gate::allows('view', $media))->toBeFalse();

    // Logged in
    $this->actingAs(User::factory()->create());
    expect(Gate::allows('view', $media))->toBeTrue();
});

it('restricts private media to creator, admin, or super_admin', function (): void {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole($this->adminRole);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole($this->superAdminRole);

    $media = CuratorMedia::factory()->create([
        'created_by' => $creator->id,
        'privacy' => Privacy::PRIVATE,
    ]);

    // Guest
    expect(Gate::allows('view', $media))->toBeFalse();

    // Other user
    $this->actingAs($otherUser);
    expect(Gate::allows('view', $media))->toBeFalse();

    // Creator
    $this->actingAs($creator);
    expect(Gate::allows('view', $media))->toBeTrue();

    // Admin
    $this->actingAs($admin);
    expect(Gate::allows('view', $media))->toBeTrue();

    // Super Admin
    $this->actingAs($superAdmin);
    expect(Gate::allows('view', $media))->toBeTrue();
});

it('restricts update and delete to creator, admin, or super_admin', function (): void {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole($this->adminRole);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole($this->superAdminRole);

    $media = CuratorMedia::factory()->create([
        'created_by' => $creator->id,
    ]);

    foreach (['update', 'delete'] as $action) {
        // Other user
        $this->actingAs($otherUser);
        expect(Gate::allows($action, $media))->toBeFalse();

        // Creator
        $this->actingAs($creator);
        expect(Gate::allows($action, $media))->toBeTrue();

        // Admin
        $this->actingAs($admin);
        expect(Gate::allows($action, $media))->toBeTrue();

        // Super Admin
        $this->actingAs($superAdmin);
        expect(Gate::allows($action, $media))->toBeTrue();
    }
});

it('syncs privacy with physical visibility', function (): void {
    // PUBLIC privacy should be public visibility
    $publicMedia = CuratorMedia::factory()->create(['privacy' => Privacy::PUBLIC]);
    expect($publicMedia->visibility)->toBe('public');

    // PRIVATE privacy should be private visibility
    $privateMedia = CuratorMedia::factory()->create(['privacy' => Privacy::PRIVATE]);
    expect($privateMedia->visibility)->toBe('private');

    // MEMBER privacy should be private visibility
    $memberMedia = CuratorMedia::factory()->create(['privacy' => Privacy::MEMBER]);
    expect($memberMedia->visibility)->toBe('private');

    // Updating privacy should update visibility
    $privateMedia->update(['privacy' => Privacy::PUBLIC]);
    expect($privateMedia->visibility)->toBe('public');

    $publicMedia->update(['privacy' => Privacy::MEMBER]);
    expect($publicMedia->visibility)->toBe('private');
});
