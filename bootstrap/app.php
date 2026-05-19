<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // 1. Trust semua proxy (wajib untuk Vercel agar CSRF/session bisa dibaca)
        $middleware->trustProxies(at: '*', headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
        );

        // 2. Alias middleware
        $middleware->alias([
            'role'        => \App\Http\Middleware\RoleMiddleware::class,
            'checkstatus' => \App\Http\Middleware\CheckUserStatus::class,
            'logactivity' => \App\Http\Middleware\LogUserActivity::class,
        ]);

        // 3. Redirect guest ke login
        $middleware->redirectGuestsTo('/login');

        // 4. Redirect user yang sudah login ke dashboard sesuai role
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if (!$user) return '/login';
            return match(strtolower($user->role)) {
                'admin'  => '/admin/dashboard',
                'bidan'  => '/bidan/dashboard',
                'kader'  => '/kader/dashboard',
                'user'   => '/user/dashboard',
                default  => '/home',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();