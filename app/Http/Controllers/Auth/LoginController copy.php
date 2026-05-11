<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $username = 'username'; // Jika login pakai username, bukan email

    public function username()
    {
        return 'username';
    }
    

    // ✅ REDIRECT BERDASARKAN ROLE
    protected function redirectTo()
{
    $user = auth()->user();
    
    // ✅ Cek level tidak null/empty sebelum bandingkan
    if ($user && !empty($user->level) && $user->level === 'admin') {
        return route('admin.dashboard');
    }
    
    return route('dashboard');
}

    // ✅ Optional: Logout redirect
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login'); // Atau route('login')
    }
}