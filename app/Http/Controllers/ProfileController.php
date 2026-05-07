<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'nip' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:100',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
        ]);

        return back()->with('success', '✅ Profil berhasil diperbarui');
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $user = auth()->user();

        // Hapus signature lama jika ada
        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        // Upload signature baru
        $signaturePath = $request->file('signature')->store('signatures', 'public');
        
        $user->update(['signature_path' => $signaturePath]);

        return back()->with('success', '✅ Tanda tangan berhasil diupload');
    }

    public function removeSignature()
    {
        $user = auth()->user();

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
            $user->update(['signature_path' => null]);
        }

        return back()->with('success', '✅ Tanda tangan berhasil dihapus');
    }
}