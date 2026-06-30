<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        

        // Cek role - case insensitive
        if (strtolower($user->role) !== strtolower($role)) {
            // Jika role tidak cocok, redirect ke dashboard role yang benar
            // bukan abort 403, agar tidak bingung
            return redirect()->to($this->getDashboardUrl($user->role))
                ->withErrors(['login' => 'Anda tidak punya akses ke halaman tersebut.']);
        }

        return $next($request);
    }

    private function getDashboardUrl(string $role): string
    {
        return match(strtolower($role)) {
            'admin'  => '/admin/dashboard',
            'bidan'  => '/bidan/dashboard',
            'kader'  => '/kader/dashboard',
            'user'   => '/user/dashboard',
            default  => '/home',
        };
    }
}