<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'   => ['*'],
    'allowed_origins'   => [
        // 'https://annaponsprojects.com',
        'http://localhost:3000',
    ],
    'allowed_origins_patterns' => [
        // '/^https?:\/\/([a-z0-9-]+\.)?annaponsprojects\.com$/',
    ],
    'allowed_headers'   => ['*'],
    'exposed_headers'   => [],
    'max_age'           => 3600,
    'supports_credentials' => false,
];
