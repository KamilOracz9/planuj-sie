<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route registration
    |--------------------------------------------------------------------------
    |
    | Set to false to disable automatic route registration and mount
    | Kamiloracz9\MediaGallery\Http\Controllers\* yourself instead.
    |
    */

    'register_routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Migration registration
    |--------------------------------------------------------------------------
    |
    | Set to false if your app already has `galleries`/`media_folders` tables
    | (e.g. migrating an existing in-house gallery onto this package) so the
    | package doesn't try to create them again.
    |
    */

    // This app's own pre-existing migrations already created `galleries`
    // and `media_folders` with the same schema — don't let the package
    // try to create them again.
    'register_migrations' => false,

    // Media lives on the private 'media' disk (see the api-security skill) -
    // served through the app's own authenticated route, not a raw disk URL.
    'media_url_route' => 'media.show',

    // routes/api.php gets an automatic 'api' URL prefix from
    // bootstrap/app.php's withRouting(api: ...); this package's routes are
    // registered independently via its own service provider, so the
    // 'api/' prefix has to be included here explicitly to match every
    // other endpoint in this app.
    'route_prefix' => 'api/gallery',

    // JWT-protected like every other endpoint in this API (see the
    // api-security skill) — 'api' middleware group + 'auth:api' guard.
    'middleware' => ['api', 'auth:api'],

    /*
    |--------------------------------------------------------------------------
    | Collections
    |--------------------------------------------------------------------------
    |
    | Each key is a Spatie media collection name on the Gallery singleton.
    | Add as many as you like (e.g. 'videos') — no code changes needed,
    | the controllers and folder tree are generic over these keys.
    |
    | 'max_size' is in kilobytes (Laravel's `max:` validation rule unit).
    | 'conversions' registers named Spatie conversions for that collection:
    | ['name' => ['width' => int, 'height' => int, 'fit' => 'crop'|'contain']].
    |
    */

    'collections' => [

        'images' => [
            'mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
            'max_size' => 10240,
            'conversions' => [
                'thumb' => ['width' => 300, 'height' => 300, 'fit' => 'crop'],
            ],
        ],

        'documents' => [
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'text/csv',
            ],
            'max_size' => 20480,
            'conversions' => [],
        ],

    ],

];
