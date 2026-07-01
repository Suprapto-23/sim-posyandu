<?php

// Matikan error reporting di production agar response lebih cepat
ini_set('display_errors', '0');

/*
|--------------------------------------------------------------------------
| Vercel Writable Runtime Directories
|--------------------------------------------------------------------------
*/
$paths = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
    '/tmp/storage/framework/cache/laravel-excel',
];

foreach ($paths as $path) {
    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

/*
|--------------------------------------------------------------------------
| Bootstrap Laravel manual (WAJIB, jangan diganti require public/index.php)
|--------------------------------------------------------------------------
| useStoragePath() harus dipanggil SEBELUM handleRequest(). public/index.php
| standar tidak melakukan ini, sehingga Laravel menulis ke storage/ bawaan
| project yang read-only di Vercel -> 500.
*/
define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp/storage');

$app->handleRequest(Illuminate\Http\Request::capture());