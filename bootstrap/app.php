<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$appStoragePath = $_ENV['APP_STORAGE']
    ?? $_SERVER['APP_STORAGE']
    ?? dirname(__DIR__) . '/storage';

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->trustProxies(
            at: '*',
            headers:
                \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->trustHosts(at: [
            'sim-posyandu.vercel.app',
            'posyandu-magement-sistem.vercel.app',
            '.vercel.app',
            'localhost',
            '127.0.0.1',
        ]);

        $middleware->validateCsrfTokens(except: [
            'login',
            'login/*',
        ]);

        $middleware->alias([
            'role'        => \App\Http\Middleware\RoleMiddleware::class,
            'checkstatus' => \App\Http\Middleware\CheckUserStatus::class,
            'logactivity' => \App\Http\Middleware\LogUserActivity::class,
        ]);

        $middleware->redirectGuestsTo('/login');

        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            if (! $user) {
                return '/login';
            }

            return match (strtolower($user->role)) {
                'admin' => '/admin/dashboard',
                'bidan' => '/bidan/dashboard',
                'kader' => '/kader/dashboard',
                'user'  => '/user/dashboard',
                default => '/home',
            };
        });

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();

/*
|--------------------------------------------------------------------------
| Vercel Runtime Fix
|--------------------------------------------------------------------------
|
| Di Vercel, beberapa binding Laravel kadang tidak kebaca normal.
| Kita paksa register filesystem dan view supaya view('...') tidak error.
|
*/

$app->useStoragePath($appStoragePath);

if (! $app->bound('files')) {
    $app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
}

if (! $app->bound('view')) {
    $app->register(\Illuminate\View\ViewServiceProvider::class);
}

return $app;