<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CuratorMedia;
use App\Models\TemporaryUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ResolveMediaAction implements \App\Actions\Contracts\ResolvesMedia
{
    public function handle(?string $uploadId, ?string $curatorId, string $purpose): ?CuratorMedia
    {
        // @codeCoverageIgnoreStart
        if ($curatorId) {
            return CuratorMedia::query()->where('id', $curatorId)->first();
        }

        // @codeCoverageIgnoreEnd

        if ($uploadId) {
            $upload = TemporaryUpload::query()->where('id', $uploadId)
                ->where('purpose', $purpose)
                ->where('status', 'uploaded')
                ->first();

            // @codeCoverageIgnoreStart
            if (! $upload) {
                return null;
            }

            // @codeCoverageIgnoreEnd

            $config = config('api-uploads.purposes.'.$purpose);
            assert(is_array($config));

            $finalDiskConfig = config('curator.disk', 'public');
            assert(is_string($finalDiskConfig));
            $finalDisk = $finalDiskConfig;

            $finalDirectoryConfig = $config['final_directory'] ?? 'media';
            assert(is_string($finalDirectoryConfig));
            $finalDirectory = $finalDirectoryConfig;

            $finalVisibility = (string) $upload->final_visibility;

            $fileName = $upload->file_name;
            assert(is_string($fileName));
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $finalFileName = Str::uuid()->toString().($extension !== '' ? '.'.$extension : '');
            $finalPath = sprintf('%s/%s', $finalDirectory, $finalFileName);

            $uploadDisk = $upload->disk;
            assert(is_string($uploadDisk));
            $tempDisk = Storage::disk($uploadDisk);
            $fileContents = $tempDisk->get($upload->path);

            // @codeCoverageIgnoreStart
            if (! $fileContents) {
                return null;
            }

            // @codeCoverageIgnoreEnd

            Storage::disk($finalDisk)->put($finalPath, $fileContents, $finalVisibility);

            $tempDisk->delete($upload->path);

            $width = null;
            $height = null;

            if (str_starts_with((string) $upload->mime_type, 'image/')) {
                /** @var string $fileContents */
                // @codeCoverageIgnoreStart
                $sizes = @getimagesizefromstring($fileContents);
                if ($sizes) {
                    $width = $sizes[0];
                    $height = $sizes[1];
                }

                // @codeCoverageIgnoreEnd
            }

            $media = CuratorMedia::query()->create([
                'disk' => $finalDisk,
                'directory' => $finalDirectory,
                'visibility' => $finalVisibility,
                'name' => pathinfo((string) $upload->file_name, PATHINFO_FILENAME),
                'path' => $finalPath,
                'width' => $width,
                'height' => $height,
                'size' => $upload->size,
                'type' => $upload->mime_type,
                'ext' => mb_strtolower($extension),
            ]);

            $upload->update([
                'status' => 'finalized',
            ]);

            return $media;
        }

        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }
}
