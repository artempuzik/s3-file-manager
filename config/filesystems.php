<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
        's3' => [
            'driver' => 's3',
            'key' => env('DO_SPACES_KEY'),
            'secret' => env('DO_SPACES_SECRET'),
            'region' => env('DO_SPACES_REGION'),
            'bucket' => env('DO_SPACES_BUCKET'),
            'endpoint' => env('DO_SPACES_ENDPOINT'),
            'path' => env('DO_SPACES_BUCKET_PDFS_PATH'),
            'public_path' => env('DO_SPACES_BUCKET_PUPLIC_PATH'),
            'path_zodiac_folder' => env('DO_SPACES_BUCKET_PDFS_PATH_ZODIAC'),
            'path_horoscope_folder' => env('DO_SPACES_BUCKET_PDFS_PATH_HOROSCOPE'),
            'path_challenge_folder' => env('DO_SPACES_BUCKET_PDFS_PATH_CHALLENGE'),
            'path_palmistry_folder' => env('DO_SPACES_BUCKET_PDFS_PATH_PALMISTRY'),
            'url' => env('DO_SPACES_URL'),
            'visibility' => 'public',
            'use_path_style_endpoint' => false,
        ],
    ],
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
