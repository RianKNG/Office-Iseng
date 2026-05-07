<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Letter;
use App\Models\User;
use Illuminate\Http\Request;

class DisposisiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function inbox()
    {
        $disposisi = Disposisi::with(['letter', 'dari'])
            ->where('ke_user_id', auth()->id())
            ->latest()
            ->paginate(10);
        return view('disposisi.inbox', compact('disposisi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'letter_id' => 'required|exists:letters,id',
            'ke_user_id' => 'required|exists:users,id',
            'instruksi' => 'required|string',
            'prioritas' => 'required|in:biasa,penting,segera,rahasia',
            'deadline' => 'nullable|date'
        ]);

        Disposisi::create([
            'letter_id' => $request->letter_id,
            'dari_user_id' => auth()->id(),
            'ke_user_id' => $request->ke_user_id,
            'instruksi' => $request->instruksi,
            'prioritas' => $request->prioritas,
            'status' => 'pending',
            'deadline' => $request->deadline,
        ]);

        // Opsional: Update status surat jika disposisi pertama kali
        $letter = Letter::find($request->letter_id);
        if($letter->status == 'draft') {
             // Logika approval bisa ditambahkan di sini tergantung bisnis proses
             // Misal: Jika staff mengirim ke Kasubag, status jadi 'menunggu_verifikasi'
             $nextLevel = auth()->user()->level_urutan + 1;
             $letter->update([
                 'status' => 'menunggu_verifikasi',
                 'current_level' => $nextLevel
             ]);
        }

        return back()->with('success', 'Disposisi berhasil dikirim.');
    }
    public function process(Request $request, $id)
{
    $disposisi = Disposisi::findOrFail($id);
    $action = $request->input('action');

    if ($action === 'forward') {
        // Logika Forward
        $instruksi = $request->input('instruksi_forward');
        $ke_user = $request->input('ke_user_id');
        // ... simpan ke database ...
        return redirect()->back()->with('success', 'Berhasil diteruskan');
    } 
    
    if ($action === 'approve') {
        // Logika Selesai
        $catatan = $request->input('catatan');
        $disposisi->update(['status' => 'selesai', 'catatan' => $catatan]);
        return redirect()->back()->with('success', 'Disposisi diselesaikan');
    }

    if ($action === 'reject') {
        // Logika Tolak
        $disposisi->update(['status' => 'ditolak']);
        return redirect()->back()->with('success', 'Disposisi dikembalikan');
    }

    // Jika sampai sini, berarti nilai 'action' tidak sesuai
    return redirect()->back()->with('error', 'Aksi tidak valid: ' . $action);
}
    
    public function ddddddprocess(Request $request, $id)
    {
       $disposisi = Disposisi::findOrFail($id);
    if($disposisi->ke_user_id != auth()->id()) { abort(403); }

    $action = $request->input('action'); 
    
    if ($action == 'approve') {
        $disposisi->update(['status' => 'diproses']);
        $letter = $disposisi->letter;
        $currentUserLevel = auth()->user()->level_urutan;
        
        if ($currentUserLevel >= 4) {
            $letter->update(['status' => 'disetujui', 'approved_by' => auth()->id()]);
        } else {
            $letter->update(['current_level' => $currentUserLevel + 1]);
        }
        return back()->with('success', 'Surat berhasil disetujui.');
    } 

    // Tambahkan logika Reject jika diperlukan
    if ($action == 'reject') {
        $disposisi->update(['status' => 'ditolak']);
        $disposisi->letter->update(['status' => 'ditolak']);
        return back()->with('warning', 'Disposisi telah ditolak.');
    }
    
    return back()->with('error', 'Aksi tidak valid.');
    }
    public function show($id)
{
    $disposisi = Disposisi::with([
        'letter.template',
        'letter.values.field',
        'letter.creator',
        'dari',
        'ke',
        'parent.dari',
        'parent.ke'
    ])->findOrFail($id);

    // Cek otorisasi - hanya pengirim atau penerima yang bisa lihat
    if ($disposisi->ke_user_id != auth()->id() && $disposisi->dari_user_id != auth()->id()) {
        abort(403, 'Anda tidak memiliki akses ke disposisi ini');
    }

    // Update status jadi 'dibaca' jika masih pending dan yang lihat adalah penerima
    if ($disposisi->status == 'pending' && $disposisi->ke_user_id == auth()->id()) {
        $disposisi->update(['status' => 'dibaca']);
    }

    return view('disposisi.show', compact('disposisi'));
}

}