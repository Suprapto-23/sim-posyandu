<?php

// Aktifkan sementara menjadi '1' jika Anda ingin melihat pesan error di Vercel
ini_set('display_errors', '0');

/*
|--------------------------------------------------------------------------
| Buat Folder Temporary untuk Vercel (Read-Only Bypass)
|--------------------------------------------------------------------------
*/
$paths = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($paths as $path) {
    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

/*
|--------------------------------------------------------------------------
| Arahkan Konfigurasi Runtime ke /tmp
|--------------------------------------------------------------------------
*/
$runtimeEnv = [
    'APP_STORAGE' => '/tmp/storage',
    'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
    'APP_PACKAGES_CACHE' => '/tmp/bootstrap/cache/packages.php',
    'APP_SERVICES_CACHE' => '/tmp/bootstrap/cache/services.php',
    'APP_CONFIG_CACHE' => '/tmp/bootstrap/cache/config.php',
    'APP_ROUTES_CACHE' => '/tmp/bootstrap/cache/routes.php',
    'APP_EVENTS_CACHE' => '/tmp/bootstrap/cache/events.php',
];

foreach ($runtimeEnv as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

/*
|--------------------------------------------------------------------------
| Jalankan Laravel 11
|--------------------------------------------------------------------------
*/
define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

// Bootstrap aplikasi dari app.php
$app = require_once __DIR__ . '/../bootstrap/app.php';

// BUG FIX: Hook paling krusial agar Laravel mau menulis di /tmp
$app->useStoragePath('/tmp/storage');

// Handle request ala Laravel 11
$app->handleRequest(Illuminate\Http\Request::capture());