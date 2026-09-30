<?php

return [
    // Master switch. Off unless explicitly enabled.
    'enabled' => env('ARTISAN_RUNNER_UI_ENABLED', FALSE),

    'path' => env('ARTISAN_RUNNER_UI_PATH', 'artisan-runner-ui'),

    // Add your own, e.g. 'can:run-artisan-runner-ui'
    'middleware' => ['web', 'auth'],

    // Anything else returns 404
    'environments' => ['local', 'qa', 'staging', 'uat'],

    // If not empty, ONLY these commands are shown/runnable (wildcards allowed)
    'allowed' => [],

    // Never shown or runnable (wildcards allowed)
    'denied' => [
        'tinker', 'serve', 'down', 'up',
        'db:wipe', 'migrate:fresh', 'migrate:reset', 'migrate:refresh', 'migrate:rollback',
        'key:generate', 'env:*', 'queue:work', 'queue:listen', 'schedule:work',
        'schedule:run', 'vendor:publish', 'package:discover',
    ],
];