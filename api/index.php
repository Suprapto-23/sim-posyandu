<?php

// 1. Siapkan folder Temporary (/tmp) untuk Vercel Read-Only Environment
$storagePath = '/tmp/storage';
$directories = [
    "$storagePath/framework/views",
    "$storagePath/framework/cache/data",
    "$storagePath/framework/sessions",
    "$storagePath/logs",
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// 2. Set Env HANYA untuk View Compiled Path (Jangan utak-atik cache bawaan Vercel)
putenv("VIEW_COMPILED_PATH=$storagePath/framework/views");
$_ENV['VIEW_COMPILED_PATH'] = "$storagePath/framework/views";
$_SERVER['VIEW_COMPILED_PATH'] = "$storagePath/framework/views";

// 3. Panggil Autoloader Composer & Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 4. Paksa Laravel menggunakan /tmp sebagai folder storage utama
$app->useStoragePath($storagePath);

// 5. Tangkap dan Jalankan Request
$app->handleRequest(Illuminate\Http\Request::capture());