<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

ini_set('display_errors', '0');

if (PHP_OS_FAMILY === 'Windows') {
    $gitPath = 'C:\\Program Files\\Git\\cmd';

    if (is_dir($gitPath)) {
        $currentPath = (string) getenv('PATH');

        if (! str_contains(strtolower($currentPath), strtolower($gitPath))) {
            putenv('PATH='.$currentPath.';'.$gitPath);
        }
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
