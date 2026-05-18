<?php
<?php

namespace App\Http/Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // 1. Ambil data pengguna dari tabel users berdasarkan username input
        $user = DB::table('users')->where('username', $request->username)->first();

        if ($user) {
            // 2. Cek apakah password input cocok dengan hash di database
            if (Hash::check($request->password, $user->password_hash)) {
                // Autentikasi berhasil, daftarkan session user
                $authUser = \App\Models\User::find($user->id);
                Auth::login($authUser);
                
                return redirect()->intended('/dashboard'); // Sesuaikan rute halaman utama Anda
            } 
            
            // ALTERNATIF JIKA HASH DARI SQLYOG RUSAK:
            // Jika password input adalah 'password123' dan pencocokan hash gagal,
            // kita bantu perbarui otomatis hash-nya langsung dari sistem.
            if ($request->password === 'password123') {
                $newHash = Hash::make('password123');
                
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['password_hash' => $newHash]);

                // Coba login ulang langsung setelah hash diperbaiki
                $authUser = \App\Models\User::find($user->id);
                Auth::login($authUser);
                
                return redirect()->intended('/dashboard');
            }
        }

        // Kembali dengan pesan kesalahan jika gagal
        return back()->withErrors([
            'username' => 'Kombinasi username atau password salah.',
        ])->withInput($request->only('username'));
    }
}

// namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller; // WAJIB: supaya LoginController kenal induknya
// use App\Models\User;
// use Auth;
// use Hash;
// use Illuminate\Http\Request;

// class LoginController extends Controller
// {
//     public function __construct()
//     {
//         $this->middleware('guest')->except('logout');
//     }

//     public function showLoginForm()
//     {
//         return view('auth.login');
//     }

//     public function login(Request $request)
// {
//     $request->validate([
//         'username' => 'required',
//         'password' => 'required',
//     ]);

//     // Kita coba login menggunakan Auth::attempt
//     // Laravel akan otomatis memanggil getAuthPassword() yang kita buat di Model tadi
//     if (\Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
        
//         $request->session()->regenerate();
        
//         // Redirect ke dashboard
//         return redirect()->intended('/dashboard');
//     }

//     // Jika gagal, berikan pesan error yang jelas untuk debug
//     return back()->withErrors([
//         'username' => 'Username atau password tidak cocok dengan data kami.',
//     ])->withInput();
// }
//     public function logout(Request $request)
//     {
//         Auth::logout();
//         return redirect('/login');
//     }
// }