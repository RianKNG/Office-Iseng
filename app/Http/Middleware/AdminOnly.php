<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        // Pastikan login dulu, baru cek kolom 'level'
        // Sesuaikan 'admin' dengan isi kolom level Anda (misal: 'admin' atau 1)
        if (Auth::check() && Auth::user()->level == 'admin') {
            return $next($request);
        }

        // Kalau bukan admin, arahkan ke home dengan pesan peringatan
        return redirect('/home')->with('error', 'Anda tidak memiliki akses level admin.');
    }
}