<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest'); // Mencegah user yang sudah login mengakses halaman register
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username'       => ['required', 'string', 'max:50', 'unique:users,name'],
            // 'nama_lengkap'   => ['required', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255', 'unique:users'], // Optional sesuai form
            'jabatan'        => ['required', 'string', 'max:100'],
            'password'       => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name'         => $data['username'],          // Simpan username ke kolom 'name'
            // 'nama_lengkap' => $data['nama_lengkap'] ?? null,
            'email'        => $data['email'] ?? null,
            'jabatan'      => $data['jabatan'] ?? 'staff',
            'password'     => Hash::make($data['password']),
            // Jika ada kolom nip/signature_path, tambahkan di sini
        ]);
    }
}