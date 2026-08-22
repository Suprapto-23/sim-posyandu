<?php

// 1. Siapkan folder Temporary (/tmp) untuk yang memang harus writable saat runtime
$storagePath = '/tmp/storage';
$bootstrapPath = '/tmp/bootstrap';

$directories = [
    "$storagePath/app/public",
    "$storagePath/framework/cache/data",
    "$storagePath/framework/sessions",
    "$storagePath/logs",
    "$bootstrapPath/cache",
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 2. Compiled VIEW diarahkan ke hasil precompile saat build (deployment bundle, read-only)
//    supaya SEMUA instance serverless menyajikan HTML yang identik, tidak compile ulang tiap request.
$viewCompiledPath = __DIR__ . '/../storage/framework/views';
putenv("VIEW_COMPILED_PATH=$viewCompiledPath");
$_ENV['VIEW_COMPILED_PATH'] = $viewCompiledPath;
$_SERVER['VIEW_COMPILED_PATH'] = $viewCompiledPath;

// 3. Cache lain (config/routes/events/services) tetap di /tmp — sengaja TIDAK di-bake saat build
//    supaya env var runtime (DB_HOST, dsb dari Vercel dashboard) tetap terbaca dengan benar.
putenv("APP_CONFIG_CACHE=$bootstrapPath/cache/config.php");
putenv("APP_EVENTS_CACHE=$bootstrapPath/cache/events.php");
putenv("APP_ROUTES_CACHE=$bootstrapPath/cache/routes.php");
putenv("APP_SERVICES_CACHE=$bootstrapPath/cache/services.php");

// 4. Panggil Autoloader Composer & Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 5. Paksa Laravel menggunakan /tmp untuk Storage dan Bootstrap (view compiled sudah dioverride di langkah 2)
$app->useStoragePath($storagePath);
$app->useBootstrapPath($bootstrapPath);

// 6. Tangkap dan Jalankan Request
$app->handleRequest(Illuminate\Http\Request::capture());