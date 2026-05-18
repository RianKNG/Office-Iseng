


<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// 1. Tampilan utama langsung oper ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Memanggil fungsi showLoginForm dari LoginController
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// 3. Aksi submit form login (POST)
Route::post('/login', [LoginController::class, 'login']);

// 4. Halaman Dashboard Sukses
Route::get('/dashboard', function () {
    return "<h2>Selamat! Anda Berhasil Masuk ke Dashboard E-OFFICE PDAM.</h2>";
})->name('dashboard');



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