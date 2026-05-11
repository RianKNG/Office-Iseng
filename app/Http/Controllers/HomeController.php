<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Letter;
use App\Models\Disposisi;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
         $user = auth()->user();
    
    // ✅ Jika admin coba akses dashboard biasa, redirect ke admin dashboard
    if ($user && $user->level === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    
    // Logic dashboard biasa untuk user non-admin
    $stats = [
        'surat_masuk' => \App\Models\Letter::where('created_by', $user->id)->count(),
        'disposisi_terima' => \App\Models\Disposisi::where('ke_user_id', $user->id)->count(),
    ];
    
    return view('home', compact('stats'));
    }
    
}