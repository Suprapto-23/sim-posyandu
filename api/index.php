<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Buat folder sementara di /tmp karena server Vercel bersifat read-only
$tmpPaths = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($tmpPaths as $path) {
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

// 2. Paksa Laravel agar menyimpan file cache/views ke folder /tmp
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');

// 3. Panggil Autoloader Composer
require __DIR__.'/../vendor/autoload.php';

// 4. Panggil file utama Aplikasi Laravel 12
$app = require_once __DIR__.'/../bootstrap/app.php';

// 5. Ubah default Storage Path ke /tmp
$app->useStoragePath('/tmp/storage');

// 6. Tangani Request dari Browser
$app->handleRequest(Request::capture());