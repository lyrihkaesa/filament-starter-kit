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

it('cannot prepare an upload with exceeding file size', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('v1.uploads.prepare'), [
        'purpose' => 'user_avatar',
        'file_name' => 'avatar.jpg',
        'content_type' => 'image/jpeg',
        'file_size' => 1024 * 1024 * 100, // Very large
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['file_size']);
});

it('cannot prepare an upload with invalid visibility', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('v1.uploads.prepare'), [
        'purpose' => 'user_avatar',
        'file_name' => 'avatar.jpg',
        'content_type' => 'image/jpeg',
        'file_size' => 1024,
        'requested_visibility' => 'invalid_visibility_type',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['requested_visibility']);
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

it('can show an upload session', function (): void {
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

    $response = $this->actingAs($user)->getJson(route('v1.uploads.show', $upload));

    $response->assertOk();
    $response->assertJsonFragment(['id' => $upload->id]);
});

it('cannot show another users upload session', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $upload = TemporaryUpload::query()->create([
        'user_id' => $otherUser->id,
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

    $response = $this->actingAs($user)->getJson(route('v1.uploads.show', $upload));

    $response->assertForbidden();
});

it('can delete an upload session', function (): void {
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

    Storage::disk('uploads_tmp')->put($upload->path, 'content');

    $response = $this->actingAs($user)->deleteJson(route('v1.uploads.destroy', $upload));

    $response->assertOk();
    $this->assertDatabaseMissing('temporary_uploads', ['id' => $upload->id]);
    Storage::disk('uploads_tmp')->assertMissing($upload->path);
});

it('cannot delete another users upload session', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $upload = TemporaryUpload::query()->create([
        'user_id' => $otherUser->id,
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

    $response = $this->actingAs($user)->deleteJson(route('v1.uploads.destroy', $upload));

    $response->assertForbidden();
});

it('does not delete file from storage when deleting finalized upload', function (): void {
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
        'status' => 'finalized',
        'final_visibility' => 'public',
    ]);

    Storage::disk('uploads_tmp')->put($upload->path, 'content');

    $response = $this->actingAs($user)->deleteJson(route('v1.uploads.destroy', $upload));

    $response->assertOk();
    Storage::disk('uploads_tmp')->assertExists($upload->path);
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
