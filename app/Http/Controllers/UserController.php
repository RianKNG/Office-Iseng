<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik'            => 'required|unique:users|max:20',
            'username'       => 'required|unique:users|max:50',
            'email'          => 'required|email|unique:users|max:100',
            'password'       => 'required|min:6',
            'nama_lengkap'   => 'required|max:100',
            'no_hp'          => 'nullable|max:20',
            'jabatan'        => 'required|max:100',
            'struktur'       => 'required|in:pusat,cabang',
            'cabang_id'      => 'nullable|integer',
            'jabatan_id'     => 'nullable|integer',
            'unit_kerja'     => 'required|max:50',
            'level'          => 'required|in:admin,dirut,kabag,kasubag,kasie,staff',
            'status'         => 'required|in:aktif,nonaktif',
            'foto_profile'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['password_hash'] = Hash::make($validated['password']);
        unset($validated['password']);

        if ($request->hasFile('foto_profile')) {
            $validated['foto_profile'] = $request->file('foto_profile')->store('public/fotos');
        }
        if ($request->hasFile('signature')) {
            $validated['signature'] = $request->file('signature')->store('public/signatures');
        }

        User::create($validated);
        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nik'            => 'required|max:20|unique:users,nik,'.$user->id,
            'username'       => 'required|max:50|unique:users,username,'.$user->id,
            'email'          => 'required|email|max:100|unique:users,email,'.$user->id,
            'password'       => 'nullable|min:6',
            'nama_lengkap'   => 'required|max:100',
            'no_hp'          => 'nullable|max:20',
            'jabatan'        => 'required|max:100',
            'struktur'       => 'required|in:pusat,cabang',
            'cabang_id'      => 'nullable|integer',
            'jabatan_id'     => 'nullable|integer',
            'unit_kerja'     => 'required|max:50',
            'level'          => 'required|in:admin,dirut,kabag,kasubag,kasie,staff',
            'status'         => 'required|in:aktif,nonaktif',
            'foto_profile'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->filled('password')) {
            $validated['password_hash'] = Hash::make($validated['password']);
        }
        unset($validated['password']);

        if ($request->hasFile('foto_profile')) {
            if ($user->foto_profile) Storage::delete('public/'.$user->foto_profile);
            $validated['foto_profile'] = $request->file('foto_profile')->store('public/fotos');
        }
        if ($request->hasFile('signature')) {
            if ($user->signature) Storage::delete('public/'.$user->signature);
            $validated['signature'] = $request->file('signature')->store('public/signatures');
        }

        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->foto_profile) Storage::delete('public/'.$user->foto_profile);
        if ($user->signature) Storage::delete('public/'.$user->signature);
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}