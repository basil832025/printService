<?php

return [
    'active_kid' => env('PRINT_ACTIVATION_ACTIVE_KID', 'v1'),
    'default_ttl_hours' => (int) env('PRINT_ACTIVATION_TTL_HOURS', 24),
    'default_one_time' => (bool) env('PRINT_ACTIVATION_ONE_TIME_DEFAULT', true),
    'keyring_json' => env('PRINT_ACTIVATION_KEYRING_JSON', ''),
];
