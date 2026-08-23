<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        // Produksi gratis memakai bucket Supabase S3 privat. Secara lokal tetap
        // memakai lokasi private Laravel agar pengembangan tidak memerlukan cloud.
        'payment_evidence' => [
            'driver' => env('PAYMENT_EVIDENCE_DRIVER', 'local'),
            'root' => env('PAYMENT_EVIDENCE_PATH', storage_path('app/private')),
            'key' => env('PAYMENT_EVIDENCE_KEY'),
            'secret' => env('PAYMENT_EVIDENCE_SECRET'),
            'region' => env('PAYMENT_EVIDENCE_REGION', 'ap-southeast-1'),
            'bucket' => env('PAYMENT_EVIDENCE_BUCKET'),
            'endpoint' => env('PAYMENT_EVIDENCE_ENDPOINT'),
            'use_path_style_endpoint' => env('PAYMENT_EVIDENCE_USE_PATH_STYLE', true),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        // Media katalog baru disimpan di object storage. Gambar katalog lama
        // tetap berasal dari public/images/katalog/curated.
        'catalog_images' => [
            'driver' => env('CATALOG_IMAGES_DRIVER', 'local'),
            'root' => env('CATALOG_IMAGES_PATH', storage_path('app/public')),
            'url' => env('CATALOG_IMAGES_PUBLIC_URL', env('APP_URL').'/storage'),
            'key' => env('CATALOG_IMAGES_KEY', env('PAYMENT_EVIDENCE_KEY')),
            'secret' => env('CATALOG_IMAGES_SECRET', env('PAYMENT_EVIDENCE_SECRET')),
            'region' => env('CATALOG_IMAGES_REGION', env('PAYMENT_EVIDENCE_REGION', 'ap-southeast-1')),
            'bucket' => env('CATALOG_IMAGES_BUCKET', 'catalog-images'),
            'endpoint' => env('CATALOG_IMAGES_ENDPOINT', env('PAYMENT_EVIDENCE_ENDPOINT')),
            'use_path_style_endpoint' => env('CATALOG_IMAGES_USE_PATH_STYLE', true),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
