<?php

declare(strict_types=1);

use App\Actions\ResolveMediaAction;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

it('can prepare an upload session', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('v1.uploads.prepare'), [
        'purpose' => 'user_avatar',
        'file_name' => 'avatar.jpg',
        'content_type' => 'image/jpeg',
        'file_size' => 1024,
        'requested_visibility' => 'public',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'message',
        'data' => [
            'upload_id',
            'purpose',
            'status',
            'upload_url',
        ],
    ]);

    $this->assertDatabaseHas('temporary_uploads', [
        'user_id' => $user->id,
        'purpose' => 'user_avatar',
        'status' => 'prepared',
    ]);
});

it('cannot prepare an upload with invalid mime type', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('v1.uploads.prepare'), [
        'purpose' => 'user_avatar',
        'file_name' => 'avatar.pdf',
        'content_type' => 'application/pdf',
        'file_size' => 1024,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['content_type']);
});

it('can upload file to local fallback and mark it as uploaded', function (): void {
    Storage::fake('uploads_tmp');

    $user = User::factory()->create();
    $upload = TemporaryUpload::query()->create([
        'user_id' => $user->id,
        'session_id' => (string) str()->uuid(),
        'disk' => 'uploads_tmp',
        'path' => 'tmp/uploads/user_avatar/test.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 1024,
        'purpose' => 'user_avatar',
        'status' => 'prepared',
        'final_visibility' => 'public',
    ]);

    $content = 'fake image content';

    $response = $this->actingAs($user)->call(
        'PUT',
        URL::signedRoute('v1.uploads.file', ['upload' => $upload->id]),
        [],
        [],
        [],
        [],
        $content
    );

    $response->assertOk();
    Storage::disk('uploads_tmp')->assertExists($upload->path);

    $markResponse = $this->actingAs($user)->postJson(route('v1.uploads.mark-uploaded', $upload));
    $markResponse->assertOk();

    $this->assertDatabaseHas('temporary_uploads', [
        'id' => $upload->id,
        'status' => 'uploaded',
    ]);
});

it('can resolve a temporary upload into curator media', function (): void {
    Storage::fake('uploads_tmp');
    Storage::fake('public');

    $user = User::factory()->create();
    $upload = TemporaryUpload::query()->create([
        'user_id' => $user->id,
        'session_id' => (string) str()->uuid(),
        'disk' => 'uploads_tmp',
        'path' => 'tmp/uploads/user_avatar/test.jpg',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
        'purpose' => 'user_avatar',
        'status' => 'uploaded',
        'final_visibility' => 'public',
    ]);

    Storage::disk('uploads_tmp')->put($upload->path, 'fake image content');

    $action = new ResolveMediaAction();
    $media = $action->execute($upload->id, null, 'user_avatar');

    expect($media)->not->toBeNull();
    expect($media->name)->toBe('test');
    expect($media->ext)->toBe('jpg');

    Storage::disk('public')->assertExists($media->path);
    Storage::disk('uploads_tmp')->assertMissing($upload->path);

    $this->assertDatabaseHas('temporary_uploads', [
        'id' => $upload->id,
        'status' => 'finalized',
    ]);
});
