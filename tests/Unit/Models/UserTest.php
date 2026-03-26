<?php

declare(strict_types=1);

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

it('returns null filament avatar url when avatar is null', function (): void {
    $user = User::factory()->create(['avatar' => null]);

    expect($user->getFilamentAvatarUrl())->toBeNull();
});

it('returns temporary filament avatar url when using local disk', function (): void {
    Storage::fake('local');
    config(['filament.default_filesystem_disk' => 'local']);

    $user = User::factory()->create(['avatar' => 'avatars/test.jpg']);

    expect($user->getFilamentAvatarUrl())->toContain('avatars/test.jpg');
});

it('returns direct filament avatar url when not using local disk', function (): void {
    Storage::fake('s3');
    config(['filament.default_filesystem_disk' => 's3']);

    $user = User::factory()->create(['avatar' => 'avatars/test.jpg']);

    expect($user->getFilamentAvatarUrl())->toBe(Storage::disk('s3')->url('avatars/test.jpg'));
});

it('anonymizes instead of force deleting', function (): void {
    $user = User::factory()->create([
        'name' => 'Should Be Anonymized',
    ]);

    $user->forceDelete();

    // Record still exists but is anonymized
    $user->refresh();
    expect($user->exists)->toBeTrue()
        ->and($user->name)->not->toBe('Should Be Anonymized')
        ->and($user->isAnonymous())->toBeTrue();
});
