<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ==== FIX: Vercel filesystem read-only, storage harus diarahkan ke /tmp ====
$storagePath = '/tmp/storage';

if (!is_dir($storagePath)) {
    mkdir($storagePath, 0755, true);
    mkdir($storagePath . '/framework/cache/data', 0755, true);
    mkdir($storagePath . '/framework/sessions', 0755, true);
    mkdir($storagePath . '/framework/testing', 0755, true);
    mkdir($storagePath . '/framework/views', 0755, true);
    mkdir($storagePath . '/logs', 0755, true);
}

// Register autoload composer
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel app
$app = require_once __DIR__ . '/../bootstrap/app.php';

// ==== FIX: override path storage secara paksa sebelum kernel jalan ====
$app->useStoragePath($storagePath);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
);

$response->send();

$kernel->terminate($request, $response);