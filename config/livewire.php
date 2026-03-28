<?php

declare(strict_types=1);

return [
    'temporary_file_upload' => [
        /*
         * Curator reads metadata from Livewire's temporary upload object after the
         * file is stored to its final location. Keeping the temporary uploads on a
         * dedicated disk prevents same-disk moves from invalidating that metadata.
         */
        'disk' => env('LIVEWIRE_UPLOAD_TMP_DISK', 'uploads_tmp'),
        'directory' => env('LIVEWIRE_UPLOAD_TMP_DIRECTORY', 'livewire-tmp'),
        'rules' => null,
        'middleware' => 'throttle:60,1',
        'preview_mimes' => [
            'png',
            'gif',
            'bmp',
            'svg',
            'wav',
            'mp4',
            'mov',
            'avi',
            'wmv',
            'mp3',
            'm4a',
            'jpg',
            'jpeg',
            'mpga',
            'webp',
            'wma',
        ],
        'max_upload_time' => 5,
        'cleanup' => true,
    ],
];
