<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // 👇 هنا كنسمحو غير للفرونت أثناء التطوير
    'allowed_origins' => [env('FRONT_URL', 'http://localhost:3000')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // 👇 باش axios withCredentials تبقى خدامة
    'supports_credentials' => true,
];
