<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Livewire Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Use 'local' disk for temporary uploads to avoid S3 ACL issues when the
    | bucket has "Bucket owner enforced" (ACLs disabled). Files upload to
    | the server first, then are moved to S3 when the form is saved.
    |
    */

    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMP_UPLOAD_DISK', 'local'),
        'directory' => 'livewire-tmp',
        'rules' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
    ],

];
