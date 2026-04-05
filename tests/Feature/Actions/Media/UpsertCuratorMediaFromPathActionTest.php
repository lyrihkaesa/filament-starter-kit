<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Media;

use App\Actions\Media\UpsertCuratorMediaFromPathAction;
use App\Models\CuratorMedia;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToCheckExistence;
use Mockery;

it('returns null when the file existence cannot be verified', function (): void {
    $path = 'avatars/test.png';
    $disk = 's3';

    $storageMock = Mockery::mock(Filesystem::class);
    $storageMock->shouldReceive('exists')
        ->with($path)
        ->andThrow(UnableToCheckExistence::forLocation($path));

    Storage::shouldReceive('disk')
        ->with($disk)
        ->andReturn($storageMock);

    $action = new UpsertCuratorMediaFromPathAction();
    $result = $action->handle(path: $path, disk: $disk);

    expect($result)->toBeNull();
});

it('returns null when the file does not exist', function (): void {
    $path = 'avatars/missing.png';
    $disk = 'public';

    Storage::fake($disk);

    $action = new UpsertCuratorMediaFromPathAction();
    $result = $action->handle(path: $path, disk: $disk);

    expect($result)->toBeNull();
});

it('returns null when path is blank', function (): void {
    $action = new UpsertCuratorMediaFromPathAction();
    $result = $action->handle(path: '', disk: 'public');

    expect($result)->toBeNull();
});

it('returns existing media if already exists', function (): void {
    Storage::fake('public');

    $media = CuratorMedia::factory()->create([
        'disk' => 'public',
        'path' => 'existing.png',
    ]);

    $action = new UpsertCuratorMediaFromPathAction();
    $result = $action->handle(path: 'existing.png', disk: 'public');

    expect($result->id)->toBe($media->id);
});

it('uses original file name when provided', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('random-uuid.png', 'fake image content');

    $action = new UpsertCuratorMediaFromPathAction();
    $result = $action->handle(path: 'random-uuid.png', originalFileName: 'my-avatar.png', disk: 'public');

    expect($result->title)->toBe('my-avatar');
    expect($result->name)->toBe('random-uuid');
});

it('handles non-image files correctly', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('document.pdf', 'fake pdf content');

    $action = new UpsertCuratorMediaFromPathAction();
    $result = $action->handle(path: 'document.pdf', disk: 'public');

    expect($result->type)->toBe('application/pdf');
    expect($result->width)->toBeNull();
    expect($result->height)->toBeNull();
});

it('respects private visibility and privacy setting', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('private.png', 'fake image content');

    $action = new UpsertCuratorMediaFromPathAction();
    $result = $action->handle(path: 'private.png', disk: 'local', visibility: 'private');

    expect($result->visibility)->toBe('private');
    expect($result->privacy->value)->toBe('private');
});
