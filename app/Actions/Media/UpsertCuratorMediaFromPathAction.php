<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\CuratorMedia;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final readonly class UpsertCuratorMediaFromPathAction
{
    public function handle(
        ?string $path,
        ?string $originalFileName = null,
        string $disk = 'public',
        string $visibility = 'public',
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

        throw_unless(
            $storage->exists($path),
            RuntimeException::class,
            sprintf('Uploaded file [%s] was not found on disk [%s].', $path, $disk),
        );

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

        /** @var CuratorMedia $media */
        $media = CuratorMedia::query()->create([
            'disk' => $disk,
            'directory' => $directory,
            'visibility' => $visibility,
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
