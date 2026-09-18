<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Credentials
    |--------------------------------------------------------------------------
    |
    | Used by App\Services\Cloudinary\CloudinaryUploader to sign requests
    | against Cloudinary's REST Upload API directly (no SDK). Never commit
    | real values — set them in your local, uncommitted .env file.
    |
    */

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),

    'api_key' => env('CLOUDINARY_API_KEY'),

    'api_secret' => env('CLOUDINARY_API_SECRET'),

];
