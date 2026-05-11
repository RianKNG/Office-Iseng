<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Letter;
use App\Models\LetterValue;
use App\Models\Template;
use App\Models\User;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisposisiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function inbox()
{
    $user = auth()->user();
    
    $query = Disposisi::with(['letter', 'dari', 'ke']);
    
    // ✅ ADMIN: Lihat SEMUA disposisi masuk
    if (!$user->isAdmin()) {
        $query->where('ke_user_id', auth()->id());
    }
    
    $disposisi = $query->orderBy('created_at', 'desc')->paginate(10);
    
    return view('disposisi.inbox', compact('disposisi'));
}

    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:templates,id',
            'nomor_surat' => 'required|string|max:100',
            'tanggal'     => 'required|date',
            'perihal'     => 'required|string|max:255',
            'ke_user_id'  => 'required|exists:users,id',
            'fields.*'    => 'nullable|string|max:1000',
            'file_path'   => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
        ]);

        DB::beginTransaction();
        try {
            $template = Template::findOrFail($request->template_id);
            $filePath = $request->hasFile('file_path') 
                ? $request->file('file_path')->store('letters', 'public') 
                : null;

            $letter = Letter::create([
                'template_id'   => $request->template_id,
                'nomor_surat'   => $request->nomor_surat,
                'tanggal'       => $request->tanggal,
                'perihal'       => $request->perihal,
                'jenis'         => $template->jenis,
                'status'        => 'menunggu_verifikasi',
                'current_level' => 1,
                'created_by'    => auth()->id(),
                'file_path'     => $filePath,
            ]);

            if ($request->has('fields') && is_array($request->fields)) {
                foreach ($request->fields as $fieldId => $value) {
                    if ($value !== null && $value !== '') {
                        LetterValue::create([
                            'letter_id' => $letter->id,
                            'field_id'  => $fieldId,
                            'nilai'     => is_string($value) ? trim($value) : $value
                        ]);
                    }
                }
            }

            // ✅ VALIDASI ROUTING SAAT CREATE SURAT BARU
            $sender = auth()->user();
            $target = User::find($request->ke_user_id);
            if ($target && !$sender->canForwardTo($target)) {
                DB::rollBack();
                return back()->withInput()->with('error', 
                    '❌ Anda tidak dapat meneruskan surat ke user ini (beda struktur/unit).');
            }

            Disposisi::create([
                'letter_id'      => $letter->id,
                'dari_user_id'   => auth()->id(),
                'ke_user_id'     => $request->ke_user_id,
                'instruksi'      => 'Surat baru - mohon ditindaklanjuti',
                'prioritas'      => 'biasa',
                'status'         => 'pending',
                'deadline'       => now()->addDays(3),
            ]);

            $this->syncLetterStatus($letter->id);
            DB::commit();
            return redirect()->route('letters.index')->with('success', '✅ Surat berhasil dibuat dan diteruskan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'status'     => 'nullable|in:pending,dibaca,diproses,diteruskan,dikembalikan,selesai',
            'action'     => 'required|in:approve,forward,return,reject',
            'instruksi'  => 'nullable|string|max:500',
            'ke_user_id' => 'nullable|exists:users,id',
            'prioritas'  => 'nullable|in:biasa,penting,segera,rahasia',
        ]);

        $disposisi = Disposisi::with('letter')->findOrFail($id);
        $user      = auth()->user();

        // 🔒 VALIDASI ROUTING BERDASARKAN STRUKTUR & UNIT
if ($request->filled('ke_user_id')) {
    $targetUser = User::find($request->ke_user_id);
    
    if ($targetUser && !$user->canForwardTo($targetUser)) {
        // Menggunakan switch untuk kompatibilitas PHP 7.x
        switch ($user->level) {
            case 'staff':
                $msg = '❌ Staff hanya bisa meneruskan ke atasan langsung di unit & struktur yang sama.';
                break;
            case 'kasubag_kasie':
                $msg = '❌ Kasubag/Kasie hanya bisa meneruskan dalam unit & struktur yang sama.';
                break;
            case 'kabag_kacab':
                $msg = '❌ Kabag/Kacab hanya bisa meneruskan lintas unit (struktur sama) atau ke Direktur.';
                break;
            default:
                $msg = '❌ Routing tidak diizinkan.';
        }
        
        return redirect()->back()->with('error', $msg);
    }
}
     
        // ==========================================
        // 🟢 DEFINISI HAK AKSES BERDASARKAN level_urutan
        // ==========================================
        $isLeader   = $user->level_urutan >= 3; // Kabag / Dirut (Bisa Disposisi)
        $isVerifier = $user->level_urutan >= 2; // Kasubag ke atas (Bisa Filter/Verifikasi)

        // ==========================================
        // 🟢 GUARD RULES
        // ==========================================
        // Hanya Kasubag/Kasie yang boleh mengembalikan ke Staff
        if ($request->action === 'return' && $user->level !== 'kasubag_kasie') {
            return redirect()->back()->with('error', '❌ Hanya Kasubag/Kasie yang boleh mengembalikan ke Staff.');
        }

        // Forward boleh dilakukan semua level verifikator+, tapi instruksi disposisi resmi hanya Leader
        if ($request->action === 'forward' && !$isVerifier) {
            return redirect()->back()->with('error', '❌ Anda tidak memiliki wewenang untuk meneruskan disposisi.');
        }

        // ==========================================
        // 🟢 TENTUKAN STATUS TARGET
        // ==========================================
        if ($request->action === 'approve' || $request->action === 'forward') {
                $targetStatus = 'diteruskan';
            } elseif ($request->action === 'return') {
                $targetStatus = 'dikembalikan';
            } elseif ($request->action === 'reject') {
                $targetStatus = 'selesai';
            } else {
                $targetStatus = 'diproses';
            }

        DB::beginTransaction();
        try {
            // 1. Update disposisi aktif
            $updateData = ['status' => $targetStatus, 'updated_at' => now()];
            
            // Simpan Instruksi (Hanya Leader yang instruksinya jadi Disposisi Resmi)
            if ($request->filled('instruksi')) {
                if ($isLeader) {
                    $updateData['instruksi'] = $request->instruksi; 
                } else {
                    // Kasubag forward pakai instruksi -> simpan sebagai catatan verifikator
                    $updateData['instruksi'] = '[Verifikator: ' . $user->nama_lengkap . '] ' . $request->instruksi;
                }
            }

            $disposisi->update($updateData);

            // 2. ROUTING CHILD DISPOSISI
            if (in_array($request->action, ['forward', 'return']) && $request->filled('ke_user_id')) {
                $nextUser = User::findOrFail($request->ke_user_id);
                
                Disposisi::create([
                    'letter_id'    => $disposisi->letter_id,
                    'parent_id'    => $disposisi->id,
                    'dari_user_id' => $user->id,
                    'ke_user_id'   => $nextUser->id,
                    'instruksi'    => $request->action === 'return' 
                        ? 'Revisi: ' . ($request->instruksi ?? 'Perbaiki sesuai ketentuan')
                        : ($request->instruksi ?? 'Mohon ditindaklanjuti'),
                    'status'       => $request->action === 'return' ? 'draft' : 'pending',
                    'prioritas'    => $request->prioritas ?? 'biasa',
                    'deadline'     => $request->action !== 'return' ? ($request->deadline ?? now()->addDays(3)) : null,
                ]);
            }

            // 3. SINKRONISASI STATUS SURAT
            if ($request->action === 'reject') {
                $disposisi->letter->update(['status' => 'selesai']); // Tolak Final
            } elseif ($targetStatus === 'dikembalikan') {
                $disposisi->letter->update(['status' => 'diproses']); // Kembali ke Staff (Open Loop)
            } else {
                $this->syncLetterStatus($disposisi->letter_id);
            }

            // 4. NOTIFIKASI
            if ($request->filled('ke_user_id') && $request->action !== 'reject') {
                app(NotifikasiService::class)->kirim(
                    User::find($request->ke_user_id),
                    $disposisi->letter,
                    $disposisi,
                    $request->action === 'return' ? 'Surat dikembalikan untuk revisi' : 'Tugas/disposisi baru'
                );
            }

            DB::commit();
            return redirect()->back()->with('success', '✅ Proses berhasil');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Process Error: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'instruksi' => 'required|string|max:500',
            'prioritas' => 'nullable|in:biasa,penting,segera',
        ]);

        $disposisi = Disposisi::findOrFail($id);

        DB::beginTransaction();
        try {
            Disposisi::create([
                'letter_id'      => $disposisi->letter_id,
                'parent_id'      => $disposisi->id,
                'dari_user_id'   => auth()->id(),
                'ke_user_id'     => $disposisi->dari_user_id,
                'instruksi'      => $request->instruksi,
                'prioritas'      => $request->prioritas ?? 'biasa',
                'status'         => 'menunggu_verifikasi',
                'deadline'       => now()->addDays(3),
                'balasan'        => '1',
            ]);

            DB::commit();
            return redirect()->back()->with('success', '✅ Balasan berhasil dikirim');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    private function syncLetterStatus($letterId)
    {
        $letter = Letter::find($letterId);
        if (!$letter) return;

        $allDisposisis = Disposisi::where('letter_id', $letterId)->get();
        
        // Status aktif yang masih perlu diproses (exclude 'selesai' & 'dikembalikan')
        $activeStatuses = ['pending', 'dibaca', 'diproses', 'diteruskan'];
        $hasActive = $allDisposisis->whereIn('status', $activeStatuses)->isNotEmpty();

        // Cek apakah ada yang reject final
        $hasRejected = $allDisposisis->where('status', 'selesai')
                            ->whereNotNull('instruksi')
                            ->isNotEmpty();

        if ($hasActive) {
            $newStatus = 'diproses';
        } elseif ($hasRejected) {
            $newStatus = 'ditolak';
        } else {
            $newStatus = 'disetujui';
            
            // ✅ CATAT APPROVAL: siapa yang menyetujui final
            $finalApprover = Disposisi::where('letter_id', $letterId)
                ->whereIn('status', ['diteruskan', 'selesai'])
                ->whereHas('ke', fn($q) => $q->whereIn('level', ['kabag_kacab', 'dirut']))
                ->latest('updated_at')
                ->first();
                
            if ($finalApprover && !$letter->approved_by) {
                $letter->update([
                    'approved_by' => $finalApprover->ke_user_id,
                    'approved_at' => $finalApprover->updated_at,
                ]);
            }
        }
        
        if ($letter->status !== $newStatus) {
            $letter->update(['status' => $newStatus]);
            Log::info("✅ Letter #{$letterId} sync to: {$newStatus}");
        }
    }
    public function show($id)
{
    $disposisi = Disposisi::with([
        'letter.template',
        'letter.values.field',
        'letter.creator',
        'letter.approver',  // ✅ Pastikan relasi ini ada di Letter.php
        'dari',
        'ke',
        'parent.dari',
        'parent.ke'
    ])->findOrFail($id);

    $user = auth()->user();

    // ✅ ADMIN: Boleh akses disposisi siapa saja
    if (!$user->isAdmin()) {
        if ($disposisi->ke_user_id != $user->id && $disposisi->dari_user_id != $user->id) {
            abort(403, 'Anda tidak memiliki akses ke disposisi ini');
        }
    }

    // Update status jadi 'dibaca' jika masih pending dan user adalah penerima
    if ($disposisi->status == 'pending' && $disposisi->ke_user_id == $user->id) {
        $disposisi->update(['status' => 'dibaca']);
    }

    // ✅ Dropdown forward: pakai method dari Model yang sudah fix
    $availableUsers = $user->getAvailableForwardTargets();

    return view('disposisi.show', compact('disposisi', 'availableUsers'));
}
// Tambah method ini di DisposisiController
public function all(Request $request)
{
    // ✅ Hanya admin yang boleh akses
    if (!auth()->user()->isAdmin()) {
        abort(403, 'Akses ditolak');
    }

    $query = Disposisi::with(['letter', 'dari', 'ke']);

    // Filter opsional
    if ($request->has('struktur')) {
        $query->whereHas('dari', fn($q) => $q->where('struktur', $request->struktur));
    }
    if ($request->has('unit_kerja')) {
        $query->whereHas('dari', fn($q) => $q->where('unit_kerja', $request->unit_kerja));
    }
    if ($request->has('search')) {
        $query->whereHas('letter', fn($q) => 
            $q->where('nomor_surat', 'like', '%'.$request->search.'%')
              ->orWhere('perihal', 'like', '%'.$request->search.'%')
        );
    }

    $disposisis = $query->latest()->paginate(20);
    return view('disposisi.all', compact('disposisis'));
}

//     public function show($id)
//     {
//         $disposisi = Disposisi::with([
//             'letter.template',
//             'letter.values.field',
//             'letter.creator',
//             'dari',
//             'ke',
//             'parent.dari',
//             'parent.ke'
//         ])->findOrFail($id);

//         if ($disposisi->ke_user_id != auth()->id() && $disposisi->dari_user_id != auth()->id()) {
//             abort(403, 'Anda tidak memiliki akses ke disposisi ini');
//         }

//         if ($disposisi->status == 'pending' && $disposisi->ke_user_id == auth()->id()) {
//             $disposisi->update(['status' => 'dibaca']);
//         }
// //////ssssssssssssssssstambahan karena user muncul semua dai ligika model----------------------------
// // 1. KITA "CIPTAKAN" VARIABELNYA DISINI
//     // Memanggil fungsi yang sudah kamu buat di Model User tadi
//         $availableUsers = auth()->user()->getAvailableForwardTargets();

//         return view('disposisi.show', compact('disposisi','availableUsers'));
//     }
}