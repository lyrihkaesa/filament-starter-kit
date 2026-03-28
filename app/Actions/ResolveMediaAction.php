<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CuratorMedia;
use App\Models\TemporaryUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ResolveMediaAction
{
    public function execute(?string $uploadId, ?string $curatorId, string $purpose): ?CuratorMedia
    {
        if ($curatorId) {
            return CuratorMedia::where('id', $curatorId)->first();
        }

        if ($uploadId) {
            $upload = TemporaryUpload::where('id', $uploadId)
                ->where('purpose', $purpose)
                ->where('status', 'uploaded')
                ->first();

            if (! $upload) {
                return null;
            }

            $config = config("api-uploads.purposes.{$purpose}");
            $finalDisk = config('curator.disk', 'public');
            $finalDirectory = $config['final_directory'] ?? 'media';
            $finalVisibility = $upload->final_visibility;

            $extension = pathinfo($upload->file_name, PATHINFO_EXTENSION);
            $finalFileName = Str::uuid()->toString().($extension ? ".{$extension}" : '');
            $finalPath = "{$finalDirectory}/{$finalFileName}";

            $tempDisk = Storage::disk($upload->disk);
            $fileContents = $tempDisk->get($upload->path);

            Storage::disk($finalDisk)->put($finalPath, $fileContents, $finalVisibility);

            $tempDisk->delete($upload->path);

            $width = null;
            $height = null;

            if (str_starts_with($upload->mime_type, 'image/')) {
                $sizes = @getimagesizefromstring($fileContents);
                if ($sizes) {
                    $width = $sizes[0];
                    $height = $sizes[1];
                }
            }

            $media = CuratorMedia::create([
                'disk' => $finalDisk,
                'directory' => $finalDirectory,
                'visibility' => $finalVisibility,
                'name' => pathinfo($upload->file_name, PATHINFO_FILENAME),
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

        return null;
    }
}
