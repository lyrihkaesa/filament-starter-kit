<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Temporary Upload Rules per Purpose
    |--------------------------------------------------------------------------
    |
    | This registry defines the validation rules and destination settings for
    | various types of mobile API file uploads based on their \"purpose\".
    |
    */

    'purposes' => [
        'user_avatar' => [
            'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size' => 2048, // KB (2 MB)
            'default_visibility' => 'public',
            'allowed_visibilities' => ['public'],
            'final_directory' => 'avatars',
        ],

        'post_thumbnail' => [
            'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size' => 3072, // KB (3 MB)
            'default_visibility' => 'public',
            'allowed_visibilities' => ['public'],
            'final_directory' => 'posts/thumbnails',
        ],

        'post_attachment' => [
            'allowed_mimes' => ['application/pdf'],
            'max_size' => 10240, // KB (10 MB)
            'default_visibility' => 'private',
            'allowed_visibilities' => ['private', 'public'],
            'final_directory' => 'posts/attachments',
        ],

        'post_video' => [
            'allowed_mimes' => ['video/mp4', 'video/quicktime'],
            'max_size' => 102400, // KB (100 MB)
            'default_visibility' => 'private',
            'allowed_visibilities' => ['private'],
            'final_directory' => 'posts/videos',
        ],
    ],
];
