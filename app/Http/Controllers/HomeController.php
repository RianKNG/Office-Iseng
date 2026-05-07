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
        
        // Hitung statistik sederhana
        $myLetters = Letter::where('created_by', $user->id)->count();
        $inboxDisposisi = Disposisi::where('ke_user_id', $user->id)
                                    ->where('status', 'pending')
                                    ->count();
        $outboxDisposisi = Disposisi::where('dari_user_id', $user->id)->count();

        return view('home', compact('myLetters', 'inboxDisposisi', 'outboxDisposisi'));
    }
}