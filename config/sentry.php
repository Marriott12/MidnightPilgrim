<?php

return [
    'dsn' => env('SENTRY_DSN'),
    'environment' => env('APP_ENV', 'production'),
    'release' => env('APP_VERSION', 'dev'),
];