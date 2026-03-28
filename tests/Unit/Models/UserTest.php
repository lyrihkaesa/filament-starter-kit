<?php

declare(strict_types=1);

use App\Models\CuratorMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('reports non-anonymous by default', function (): void {
    $user = User::factory()->create();

    expect($user->isAnonymous())->toBeFalse();
});

it('reports anonymous when anonymized_at set', function (): void {
    $user = User::factory()->create([
        'anonymized_at' => now(),
    ]);

    expect($user->isAnonymous())->toBeTrue();
});

it('can anonymize a user', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $user->anonymize();

    expect($user->name)->not->toBe('Old Name')
        ->and($user->email)->not->toBe('old@example.com')
        ->and($user->isAnonymous())->toBeTrue();
});

it('returns null filament avatar url when avatar media is missing', function (): void {
    $user = User::factory()->create();

    expect($user->getFilamentAvatarUrl())->toBeNull();
});

it('prefers curator avatar url when avatar media is attached', function (): void {
    Storage::fake('public');

    $media = CuratorMedia::query()->create([
        'disk' => 'public',
        'directory' => 'avatars',
        'visibility' => 'public',
        'name' => 'test-avatar',
        'path' => 'avatars/test-avatar.jpg',
        'size' => 1234,
        'type' => 'image/jpeg',
        'ext' => 'jpg',
    ]);

    $user = User::factory()->create([
        'avatar_curator_id' => $media->getKey(),
    ]);

    expect($user->fresh()->getFilamentAvatarUrl())->toBe($media->url);
});

it('uses direct media urls for curator image derivatives across disks', function (): void {
    $media = new CuratorMedia([
        'disk' => 'public',
        'directory' => 'avatars',
        'visibility' => 'public',
        'name' => 'test-avatar',
        'path' => 'avatars/test-avatar.jpg',
        'type' => 'image/jpeg',
        'ext' => 'jpg',
    ]);

    expect($media->thumbnail_url)->toBe($media->url)
        ->and($media->medium_url)->toBe($media->url)
        ->and($media->large_url)->toBe($media->url);
});

it('anonymizes instead of force deleting', function (): void {
    $user = User::factory()->create([
        'name' => 'Should Be Anonymized',
    ]);

    $user->forceDelete();

    // Record still exists but is anonymized (must use withTrashed() to see soft-deleted records)
    $user = User::withTrashed()->find($user->id);
    expect($user)->not->toBeNull()
        ->and($user->name)->not->toBe('Should Be Anonymized')
        ->and($user->isAnonymous())->toBeTrue();
});
