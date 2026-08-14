<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Media\CheckMediaUsageAction;
use App\Actions\Media\SyncMediaUsageAction;
use App\Models\CuratorMedia;
use App\Models\CuratorMediaUsage;
use App\Models\Permission;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('tracks media usage when SyncMediaUsageAction is executed', function (): void {
    $initialCount = CuratorMediaUsage::query()->count();
    $user = User::factory()->create();
    $media = CuratorMedia::factory()->create();

    resolve(SyncMediaUsageAction::class)->handle($user, 'avatar_curator_id', $media->id);

    expect(CuratorMediaUsage::query()->count())->toBe($initialCount + 1);

    $usage = CuratorMediaUsage::query()
        ->where('curator_media_id', $media->id)
        ->where('model_id', $user->id)
        ->first();

    expect($usage)->not->toBeNull()
        ->and($usage->model_type)->toBe($user->getMorphClass())
        ->and($usage->field_name)->toBe('avatar_curator_id');
});

it('removes media usage when SyncMediaUsageAction is executed with null', function (): void {
    $initialCount = CuratorMediaUsage::query()->count();
    $user = User::factory()->create();
    $media = CuratorMedia::factory()->create();

    // Create usage
    resolve(SyncMediaUsageAction::class)->handle($user, 'avatar_curator_id', $media->id);
    expect(CuratorMediaUsage::query()->count())->toBe($initialCount + 1);

    // Remove usage
    resolve(SyncMediaUsageAction::class)->handle($user, 'avatar_curator_id', null);
    expect(CuratorMediaUsage::query()->count())->toBe($initialCount);
});

it('prevents media deletion if it is in use', function (): void {
    $owner = User::factory()->create();
    Permission::findOrCreate('DeleteOwn:CuratorMedia', 'web');
    $owner->givePermissionTo('DeleteOwn:CuratorMedia');

    $post = Post::factory()->create();
    $media = CuratorMedia::factory()->create([
        'created_by' => $owner->id,
    ]);

    // Attach media to post (track usage)
    resolve(SyncMediaUsageAction::class)->handle($post, 'thumbnail_curator_id', $media->id);

    expect(resolve(CheckMediaUsageAction::class)->handle((string) $media->id))->toBeTrue();

    // Try to delete via policy
    $this->actingAs($owner);

    // Owner without special override permission must stay blocked.
    expect(Gate::allows('delete', $media))->toBeFalse();
});

it('cleans up usage records when a model is deleted', function (): void {
    $initialCount = CuratorMediaUsage::query()->count();
    $user = User::factory()->create();
    $media = CuratorMedia::factory()->create();

    resolve(SyncMediaUsageAction::class)->handle($user, 'avatar_curator_id', $media->id);
    expect(CuratorMediaUsage::query()->count())->toBe($initialCount + 1);

    // Delete user
    $user->delete();

    // Usage should be gone (triggered by User model deleting hook)
    expect(CuratorMediaUsage::query()->count())->toBe($initialCount);
});

it('tracks user avatar usage automatically on save', function (): void {
    $media = CuratorMedia::factory()->create();

    $user = User::factory()->create([
        'avatar_curator_id' => $media->id,
    ]);

    $usage = CuratorMediaUsage::query()
        ->where('curator_media_id', $media->id)
        ->where('model_id', $user->id)
        ->where('field_name', 'avatar_curator_id')
        ->first();

    expect($usage)->not->toBeNull();
});

it('updates user avatar usage when changed', function (): void {
    $media1 = CuratorMedia::factory()->create();
    $media2 = CuratorMedia::factory()->create();

    $user = User::factory()->create([
        'avatar_curator_id' => $media1->id,
    ]);

    expect(CuratorMediaUsage::query()->where('curator_media_id', $media1->id)->exists())->toBeTrue();

    $user->update([
        'avatar_curator_id' => $media2->id,
    ]);

    expect(CuratorMediaUsage::query()->where('curator_media_id', $media1->id)->exists())->toBeFalse()
        ->and(CuratorMediaUsage::query()->where('curator_media_id', $media2->id)->exists())->toBeTrue();
});

it('restores media usage when user is restored', function (): void {
    $media = CuratorMedia::factory()->create();
    $user = User::factory()->create([
        'avatar_curator_id' => $media->id,
    ]);

    expect(CuratorMediaUsage::query()->where('curator_media_id', $media->id)->exists())->toBeTrue();

    $user->delete();

    expect(CuratorMediaUsage::query()->where('curator_media_id', $media->id)->exists())->toBeFalse();

    $user->restore();

    expect(CuratorMediaUsage::query()->where('curator_media_id', $media->id)->exists())->toBeTrue();
});
