<?php

declare(strict_types=1);

use App\Actions\Contracts\ResolvesMedia;
use App\Models\CuratorMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('can update profile with basic data', function (): void {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'locale' => 'en',
    ]);

    Sanctum::actingAs($user, ['profile:read']);

    $response = $this->patchJson('/api/v1/me', [
        'name' => 'Updated Name',
        'locale' => 'id',
        'timezone' => 'Asia/Jakarta',
        'theme' => 'dark',
    ]);

    $response->assertSuccessful();

    $payload = $response->json();

    expect($payload['message'])->toBe('Profile updated successfully.')
        ->and($payload['data']['name'])->toBe('Updated Name')
        ->and($payload['data']['locale'])->toBe('id')
        ->and($payload['data']['timezone'])->toBe('Asia/Jakarta')
        ->and($payload['data']['theme'])->toBe('dark');

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->locale)->toBe('id')
        ->and($user->theme)->toBe('dark');
});

it('can update profile with temporary avatar upload id', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['profile:read']);

    $uploadId = (string) Str::uuid();

    // Mock ResolvesMedia
    $media = CuratorMedia::factory()->create();
    $mock = Mockery::mock(ResolvesMedia::class);
    $mock->shouldReceive('handle')
        ->once()
        ->with($uploadId, null, 'user_avatar')
        ->andReturn($media);
    app()->instance(ResolvesMedia::class, $mock);

    $response = $this->patchJson('/api/v1/me', [
        'avatar_upload_id' => $uploadId,
    ]);

    $response->assertSuccessful();

    $user->refresh();
    expect($user->avatar_curator_id)->toBe($media->id);
});

it('can update profile with existing media id', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['profile:read']);

    $media = CuratorMedia::factory()->create();

    $response = $this->patchJson('/api/v1/me', [
        'avatar_media_id' => $media->id,
    ]);

    $response->assertSuccessful();

    $user->refresh();
    expect($user->avatar_curator_id)->toBe($media->id);
});
