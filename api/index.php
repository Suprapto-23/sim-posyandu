<?php

// 1. Siapkan folder Temporary (/tmp) untuk Storage dan Bootstrap
$storagePath = '/tmp/storage';
$bootstrapPath = '/tmp/bootstrap';

$directories = [
    "$storagePath/app/public",
    "$storagePath/framework/cache/data",
    "$storagePath/framework/sessions",
    "$storagePath/framework/views",
    "$storagePath/logs",
    "$bootstrapPath/cache",
];

// Looping untuk membuat struktur folder di /tmp
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 2. Set Env untuk membelokkan semua cache framework ke /tmp
putenv("VIEW_COMPILED_PATH=$storagePath/framework/views");
putenv("APP_CONFIG_CACHE=$bootstrapPath/cache/config.php");
putenv("APP_EVENTS_CACHE=$bootstrapPath/cache/events.php");
putenv("APP_ROUTES_CACHE=$bootstrapPath/cache/routes.php");
putenv("APP_SERVICES_CACHE=$bootstrapPath/cache/services.php");

$_ENV['VIEW_COMPILED_PATH'] = "$storagePath/framework/views";
$_SERVER['VIEW_COMPILED_PATH'] = "$storagePath/framework/views";

// 3. Panggil Autoloader Composer & Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 4. Paksa Laravel menggunakan /tmp untuk Storage dan Bootstrap
$app->useStoragePath($storagePath);
$app->useBootstrapPath($bootstrapPath);

// 5. Tangkap dan Jalankan Request
$app->handleRequest(Illuminate\Http\Request::capture());