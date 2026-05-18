<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

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
        'ca_exists' => file_exists(__DIR__ . '/../config/certs/aiven-ca.pem'),
        'public_index_exists' => file_exists(__DIR__ . '/../public/index.php'),
        'base_path' => realpath(__DIR__ . '/..'),
    ], JSON_PRETTY_PRINT);

    exit;
}

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
];

foreach ($paths as $path) {
    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

putenv('APP_STORAGE=/tmp/storage');
$_ENV['APP_STORAGE'] = '/tmp/storage';
$_SERVER['APP_STORAGE'] = '/tmp/storage';

putenv('VIEW_COMPILED_PATH=/tmp/views');
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/views';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/views';

require __DIR__ . '/../public/index.php';