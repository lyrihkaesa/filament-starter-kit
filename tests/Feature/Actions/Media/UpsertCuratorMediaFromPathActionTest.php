<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Media;

use App\Actions\Media\UpsertCuratorMediaFromPathAction;
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
