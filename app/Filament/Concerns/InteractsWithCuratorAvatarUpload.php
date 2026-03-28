<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Actions\Media\UpsertCuratorMediaFromPathAction;
use App\Models\CuratorMedia;

trait InteractsWithCuratorAvatarUpload
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function fillAvatarUploadState(array $data, ?CuratorMedia $media): array
    {
        if (! ($media instanceof CuratorMedia)) {
            return $data;
        }

        $data['avatar_upload'] = $media->path;
        $data['avatar_upload_file_name'] = filled($media->title)
            ? $media->title.'.'.$media->ext
            : $media->name.'.'.$media->ext;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function resolveAvatarUploadData(array $data): array
    {
        $media = resolve(UpsertCuratorMediaFromPathAction::class)->handle(
            path: isset($data['avatar_upload']) && is_string($data['avatar_upload']) ? $data['avatar_upload'] : null,
            originalFileName: isset($data['avatar_upload_file_name']) && is_string($data['avatar_upload_file_name']) ? $data['avatar_upload_file_name'] : null,
            disk: 'public',
            visibility: 'public',
        );

        $data['avatar_curator_id'] = $media?->getKey();

        unset($data['avatar_upload'], $data['avatar_upload_file_name']);

        return $data;
    }
}
