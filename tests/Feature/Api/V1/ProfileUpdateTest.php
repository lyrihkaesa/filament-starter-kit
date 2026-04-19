<?php

declare(strict_types=1);

use App\Models\CuratorMedia;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
    Storage::fake('uploads_tmp');
    Storage::fake('public');

    $user = User::factory()->create();
    Sanctum::actingAs($user, ['profile:read']);

    // Create a real temporary upload record
    $upload = TemporaryUpload::query()->create([
        'user_id' => $user->id,
        'session_id' => (string) str()->uuid(),
        'disk' => 'uploads_tmp',
        'path' => 'tmp/avatars/avatar.jpg',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 1024,
        'purpose' => 'user_avatar',
        'status' => 'uploaded',
        'final_visibility' => 'public',
    ]);

    Storage::disk('uploads_tmp')->put($upload->path, 'fake content');

    $response = $this->patchJson('/api/v1/me', [
        'avatar_upload_id' => $upload->id,
    ]);

    $response->assertSuccessful();

    $user->refresh();
    expect($user->avatar_curator_id)->not->toBeNull();

    $media = CuratorMedia::query()->find($user->avatar_curator_id);
    expect($media)->not->toBeNull();
    Storage::disk('public')->assertExists($media->path);
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
