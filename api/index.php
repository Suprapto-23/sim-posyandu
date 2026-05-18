<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| Vercel Writable Runtime Directories
|--------------------------------------------------------------------------
|
| Vercel hanya aman menulis ke /tmp.
| Jadi semua storage/cache Laravel diarahkan ke /tmp.
|
*/

$paths = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/views',
    '/tmp/bootstrap',
    '/tmp/bootstrap/cache',
];

foreach ($paths as $path) {
    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

/*
|--------------------------------------------------------------------------
| Force Laravel runtime paths to writable /tmp
|--------------------------------------------------------------------------
*/

$runtimeEnv = [
    'APP_STORAGE' => '/tmp/storage',
    'VIEW_COMPILED_PATH' => '/tmp/views',

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
| Debug Endpoint
|--------------------------------------------------------------------------
*/

if (($_SERVER['REQUEST_URI'] ?? '') === '/_debug-vercel') {
    header('Content-Type: application/json');

    echo json_encode([
        'status' => 'vercel php runtime hidup',
        'php_version' => PHP_VERSION,
        'request_uri' => $_SERVER['REQUEST_URI'] ?? null,

        'app_env' => getenv('APP_ENV'),
        'app_debug' => getenv('APP_DEBUG'),
        'app_url' => getenv('APP_URL'),

        'db_host' => getenv('DB_HOST'),
        'db_port' => getenv('DB_PORT'),
        'db_database' => getenv('DB_DATABASE'),

        'session_driver' => getenv('SESSION_DRIVER'),
        'session_cookie' => getenv('SESSION_COOKIE'),

        'view_compiled_path' => getenv('VIEW_COMPILED_PATH'),
        'app_storage' => getenv('APP_STORAGE'),

        'packages_cache' => getenv('APP_PACKAGES_CACHE'),
        'services_cache' => getenv('APP_SERVICES_CACHE'),
        'config_cache' => getenv('APP_CONFIG_CACHE'),

        'ca_exists' => file_exists(__DIR__ . '/../config/certs/aiven-ca.pem'),
        'public_index_exists' => file_exists(__DIR__ . '/../public/index.php'),

        'tmp_bootstrap_cache_exists' => is_dir('/tmp/bootstrap/cache'),
        'tmp_bootstrap_cache_writable' => is_writable('/tmp/bootstrap/cache'),

        'base_path' => realpath(__DIR__ . '/..'),
    ], JSON_PRETTY_PRINT);

    exit;
}

require __DIR__ . '/../public/index.php';