<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Enums\Privacy;
use App\Models\CuratorMedia;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class UpsertCuratorMediaFromPathAction
{
    public function handle(
        ?string $path,
        ?string $originalFileName = null,
        string $disk = 'public',
        string $visibility = 'public',
        ?Privacy $privacy = null,
    ): ?CuratorMedia {
        if (blank($path)) {
            return null;
        }

        $existingMedia = CuratorMedia::query()
            ->where('disk', $disk)
            ->where('path', $path)
            ->first();

        if ($existingMedia instanceof CuratorMedia) {
            return $existingMedia;
        }

        $storage = Storage::disk($disk);

        try {
            $exists = $storage->exists($path);
        } catch (Throwable) {
            $exists = false;
        }

        if (! $exists) {
            return null;
        }

        $extension = mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $directory = $directory === '.' ? null : $directory;

        $fileName = pathinfo($path, PATHINFO_FILENAME);
        $title = filled($originalFileName)
            ? pathinfo($originalFileName, PATHINFO_FILENAME)
            : $fileName;

        $mimeType = (string) ($storage->mimeType($path) ?? 'application/octet-stream');
        $size = $storage->size($path);
        $dimensions = str_starts_with($mimeType, 'image/')
            ? @getimagesizefromstring((string) $storage->get($path))
            : false;

        $privacy ??= ($visibility === 'public')
            ? Privacy::PUBLIC
            : Privacy::PRIVATE;

        /** @var CuratorMedia $media */
        $media = CuratorMedia::query()->create([
            'disk' => $disk,
            'directory' => $directory,
            'visibility' => $visibility,
            'privacy' => $privacy,
            'name' => $fileName,
            'path' => $path,
            'title' => $title,
            'width' => is_array($dimensions) ? $dimensions[0] : null,
            'height' => is_array($dimensions) ? $dimensions[1] : null,
            'size' => $size,
            'type' => $mimeType,
            'ext' => $extension,
            'exif' => null,
        ]);

        return $media;
    }
}
