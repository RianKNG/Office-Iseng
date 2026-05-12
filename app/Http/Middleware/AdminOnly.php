<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOnly
{
    
       public function handle(Request $request, Closure $next)
    {
        // 1. Cek apakah sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Cek apakah levelnya 'admin'
        if (Auth::user()->level === 'admin') {
            return $next($request);
        }

        // 3. JIKA BUKAN ADMIN: Jangan lempar ke login (biar tidak loop)
        // Lempar ke dashboard utama user biasa
        return redirect()->route('dashboard')->with('error', 'Akses khusus Admin!');
    }
}