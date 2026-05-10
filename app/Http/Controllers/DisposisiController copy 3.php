<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Letter;
use App\Models\LetterValue;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisposisiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display inbox disposisi untuk user yang login
     */
    public function inbox()
    {
        $disposisi = Disposisi::with(['letter', 'dari', 'ke'])
            ->where('ke_user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('disposisi.inbox', compact('disposisi'));
    }

    /**
     * Store new letter with initial disposition
     */
    public function store(Request $request)
    {
        Log::info('=== STORE LETTER ===', ['user_id' => auth()->id()]);

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
            
            $filePath = null;
            if ($request->hasFile('file_path')) {
                $filePath = $request->file('file_path')->store('letters', 'public');
            }

            // 1. Simpan Header Surat
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

            // 2. Simpan Dynamic Fields
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

            // 3. Buat Disposisi Awal
            Disposisi::create([
                'letter_id'      => $letter->id,
                'dari_user_id'   => auth()->id(),
                'ke_user_id'     => $request->ke_user_id,
                'instruksi'      => 'Surat baru - mohon ditindaklanjuti',
                'prioritas'      => 'biasa',
                'status'         => 'pending',
                'deadline'       => now()->addDays(3),
            ]);

            // Sync status surat
            $this->syncLetterStatus($letter->id);

            DB::commit();
            Log::info('Letter created', ['letter_id' => $letter->id]);
            
            return redirect()->route('letters.index')
                ->with('success', '✅ Surat berhasil dibuat dan diteruskan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Process disposition (approve/forward/reject)
     */
    /**
 * Process disposition (approve/forward/reject)
 */
public function process(Request $request, $id)
{
    $request->validate([
        'action' => 'required|in:approve,forward,reject',
        'status' => 'required|in:pending,diproses,selesai,ditolak,diteruskan,dibaca',
        'catatan_respon' => 'nullable|string',
        'instruksi' => 'nullable|string|max:500',
        'ke_user_id' => 'nullable|exists:users,id',
        'prioritas' => 'nullable|in:biasa,penting,segera',
        'deadline' => 'nullable|date',
    ]);

    $disposisi = Disposisi::with('letter')->findOrFail($id);

    DB::beginTransaction();
    try {
        // Tentukan status akhir berdasarkan action
        $action = $request->input('action');
        $newStatus = $request->input('status');
        
        // Override status berdasarkan action untuk konsistensi
        if ($action === 'approve') {
            $newStatus = 'diteruskan';
        } elseif ($action === 'reject') {
            $newStatus = 'ditolak';
        } elseif ($action === 'forward') {
            $newStatus = 'diteruskan';
        }

        // 1. Update disposisi saat ini
        $disposisi->update([
            'status' => $newStatus,
            'catatan_respon' => $request->catatan_respon ?? $disposisi->catatan_respon,
            'updated_at' => now(),
        ]);

        // 2. Jika forward/approve dengan tujuan user lain
        if (in_array($action, ['approve', 'forward']) && $request->filled('ke_user_id')) {
            Disposisi::create([
                'letter_id'      => $disposisi->letter_id,
                'parent_id'      => $disposisi->id,
                'dari_user_id'   => auth()->id(),
                'ke_user_id'     => $request->ke_user_id,
                'instruksi'      => $request->instruksi ?? $disposisi->instruksi,
                'prioritas'      => $request->prioritas ?? $disposisi->prioritas,
                'status'         => 'pending',
                'deadline'       => $request->deadline ?? now()->addDays(3),
            ]);
        }

        // 3. Sync status surat
        $this->syncLetterStatus($disposisi->letter_id);

        DB::commit();
        
        $message = $action === 'reject' 
            ? '✅ Surat ditolak' 
            : '✅ Disposisi berhasil ' . ($action === 'forward' ? 'diteruskan' : 'disetujui');
            
        return redirect()->back()->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Process Error: ' . $e->getMessage());
        return redirect()->back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
    }
}
/**
 * Sync status surat mengikuti disposisi aktif terkini
 */
private function syncLetterStatus($letterId)
{
    $letter = Letter::find($letterId);
    if (!$letter) {
        Log::error("❌ Letter #{$letterId} NOT FOUND");
        return;
    }

    // Cek apakah status surat sudah di $fillable model Letter
    // Jika error "MassAssignmentException", tambahkan 'status' ke $fillable di Letter.php

    $allDisposisis = Disposisi::where('letter_id', $letterId)->get();

    // PRIORITAS 1: Ada yang ditolak?
    if ($allDisposisis->contains('status', 'ditolak')) {
        $newStatus = 'ditolak';
    } 
    // PRIORITAS 2: Ada yang masih aktif?
    elseif ($allDisposisis->whereIn('status', ['pending', 'diproses', 'dibaca', 'diteruskan'])->isNotEmpty()) {
        $newStatus = 'diproses'; // ✅ UBAH dari 'menunggu_verifikasi' ke 'diproses'
    } 
    // PRIORITAS 3: Semua selesai
    else {
        $newStatus = 'selesai';
    }

    // Update hanya jika berbeda
    if ($letter->status !== $newStatus) {
        Log::info("🎯 Update surat: {$letter->status} → {$newStatus}");
        $letter->update(['status' => $newStatus]);
    }
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

        // Otorisasi: hanya pengirim atau penerima yang bisa lihat
        if ($disposisi->ke_user_id != auth()->id() && $disposisi->dari_user_id != auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke disposisi ini');
        }

        // Update status jadi 'dibaca' jika masih pending dan yang lihat adalah penerima
        if ($disposisi->status == 'pending' && $disposisi->ke_user_id == auth()->id()) {
            $disposisi->update(['status' => 'dibaca']);
        }

        return view('disposisi.show', compact('disposisi'));
    }
} // ← ✅ PASTIKAN ADA CLOSING BRACE INI DI AKHIR FILE