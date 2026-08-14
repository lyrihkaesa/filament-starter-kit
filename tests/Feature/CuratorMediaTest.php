<?php

declare(strict_types=1);

use App\Actions\Media\DeleteCuratorMediaAction;
use App\Enums\Privacy;
use App\Models\CuratorMedia;
use App\Models\Permission;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

    // Create necessary permissions for testing (Following Project Convention Action:Model)
    Permission::findOrCreate('ViewAny:CuratorMedia');
    Permission::findOrCreate('View:CuratorMedia');
    Permission::findOrCreate('ViewOwn:CuratorMedia');
    Permission::findOrCreate('Update:CuratorMedia');
    Permission::findOrCreate('UpdateOwn:CuratorMedia');
    Permission::findOrCreate('Delete:CuratorMedia');
    Permission::findOrCreate('DeleteOwn:CuratorMedia');
    Permission::findOrCreate('DeleteUsed:CuratorMedia');
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

it('restricts private media based on permissions', function (): void {
    $creator = User::factory()->create();
    $creator->givePermissionTo('ViewOwn:CuratorMedia');

    $otherUser = User::factory()->create();

    $adminUser = User::factory()->create();
    $adminUser->givePermissionTo('View:CuratorMedia');

    $media = CuratorMedia::factory()->create([
        'created_by' => $creator->id,
        'privacy' => Privacy::PRIVATE,
    ]);

    // Guest
    expect(Gate::allows('view', $media))->toBeFalse();

    // Other user (no permission)
    $this->actingAs($otherUser);
    expect(Gate::allows('view', $media))->toBeFalse();

    // Creator (has view_own permission)
    $this->actingAs($creator);
    expect(Gate::allows('view', $media))->toBeTrue();

    // Admin (has view_any permission)
    $this->actingAs($adminUser);
    expect(Gate::allows('view', $media))->toBeTrue();
});

it('restricts update and delete based on any/own permissions', function (): void {
    $creator = User::factory()->create();
    $creator->givePermissionTo(['UpdateOwn:CuratorMedia', 'DeleteOwn:CuratorMedia']);

    $otherUser = User::factory()->create();

    $adminUser = User::factory()->create();
    $adminUser->givePermissionTo(['Update:CuratorMedia', 'Delete:CuratorMedia']);

    $media = CuratorMedia::factory()->create([
        'created_by' => $creator->id,
    ]);

    foreach (['Update', 'Delete'] as $action) {
        $policyAction = mb_strtolower($action);

        // Other user
        $this->actingAs($otherUser);
        expect(Gate::allows($policyAction, $media))->toBeFalse();

        // Creator
        $this->actingAs($creator);
        expect(Gate::allows($policyAction, $media))->toBeTrue();

        // Admin
        $this->actingAs($adminUser);
        expect(Gate::allows($policyAction, $media))->toBeTrue();
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
});

it('supports soft deletes and tracks who deleted it', function (): void {
    $user = User::factory()->create();
    $media = CuratorMedia::factory()->create();

    $this->actingAs($user);

    resolve(DeleteCuratorMediaAction::class)->handle($media);

    $media->refresh();
    expect($media->trashed())->toBeTrue()
        ->and($media->deleted_by)->toBe($user->id)
        ->and($media->deletedBy)->toBeInstanceOf(User::class)
        ->and($media->deletedBy->id)->toBe($user->id);
});

it('cannot delete media that is still in use', function (): void {
    $media = CuratorMedia::factory()->create();
    $post = Post::factory()->create([
        'thumbnail_curator_id' => $media->getKey(),
    ]);

    expect(resolve(DeleteCuratorMediaAction::class)->handle($media))->toBeFalse()
        ->and($media->fresh()->trashed())->toBeFalse();
});

it('allows admin to delete media that is still in use', function (): void {
    $admin = User::factory()->create();
    $admin->givePermissionTo('Delete:CuratorMedia');

    $media = CuratorMedia::factory()->create();
    $post = Post::factory()->create([
        'thumbnail_curator_id' => $media->getKey(),
    ]);

    expect(resolve(DeleteCuratorMediaAction::class)->handle($media, $admin->id, true))->toBeTrue()
        ->and($media->fresh()->trashed())->toBeTrue();
});

it('generates correct url based on visibility', function (): void {
    Date::setTestNow(now());
    Storage::fake('s3');

    // Public media
    $publicMedia = CuratorMedia::factory()->create([
        'disk' => 's3',
        'privacy' => Privacy::PUBLIC,
        'path' => 'test-public.jpg',
    ]);
    expect($publicMedia->url)->toBe(Storage::disk('s3')->url('test-public.jpg'));

    // Private media
    $privateMedia = CuratorMedia::factory()->create([
        'disk' => 's3',
        'privacy' => Privacy::PRIVATE,
        'path' => 'test-private.jpg',
    ]);
    expect($privateMedia->url)->toBe(Storage::disk('s3')->temporaryUrl('test-private.jpg', now()->addMinutes(60)));

    Date::setTestNow();
});
