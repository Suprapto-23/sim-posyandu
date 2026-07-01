<?php

// Matikan error reporting di production agar response lebih cepat
ini_set('display_errors', '0');

/*
|--------------------------------------------------------------------------
| Vercel Writable Runtime Directories (Versi Super Ringan)
|--------------------------------------------------------------------------
| Kita pangkas dari 11 folder menjadi 3 folder krusial saja.
| Session dan Cache tidak perlu folder lagi karena dialihkan ke Database.
*/

$paths = [
    '/tmp/storage/framework/views', 
    '/tmp/storage/framework/cache',       // Wajib ditambahkan
    '/tmp/storage/framework/cache/data',  // Wajib ditambahkan
    '/tmp/storage/framework/sessions',    // Wajib ditambahkan
    '/tmp/storage/logs',            
    '/tmp/bootstrap/cache',         
];

// Pengecekan is_dir ini sekarang akan berjalan sangat cepat
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

// Hapus blok Debugging bawaan yang memakan resource, langsung tembak ke aplikasi utama
require __DIR__ . '/../public/index.php';