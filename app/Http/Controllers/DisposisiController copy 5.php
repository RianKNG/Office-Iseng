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
// public function process(Request $request, $id)
// {
//     // Validasi lebih fleksibel
//     $request->validate([
//         'status'         => 'nullable|in:pending,diproses,selesai,ditolak,diteruskan,dibaca',
//         'action'         => 'nullable|in:approve,forward,reject',
//         'catatan_respon' => 'nullable|string',
//         'instruksi'      => 'nullable|string|max:500',
//         'ke_user_id'     => 'nullable|exists:users,id',
//         'prioritas'      => 'nullable|in:biasa,penting,segera',
//     ]);

//     $disposisi = Disposisi::with('letter')->findOrFail($id);
    
//     // Tentukan status berdasarkan action atau input
//     $targetStatus = $request->status;
    
//    if (!$targetStatus && $request->has('action')) {
//     $action = $request->action;
//     if ($action === 'approve' || $action === 'forward') {
//         $targetStatus = 'diteruskan';
//     } elseif ($action === 'reject') {
//         $targetStatus = 'ditolak';
//     } else {
//         $targetStatus = 'diproses';
//     }
// }

//     DB::beginTransaction();
//     try {
//         // 1. Update disposisi saat ini
//         $disposisi->update([
//             'status'         => $targetStatus,
//             'catatan_respon' => $request->catatan_respon ?? $disposisi->catatan_respon,
//             'updated_at'     => now(),
//         ]);

//         // 2. Jika ada forward ke user lain
//         if ($targetStatus !== 'ditolak' && $request->filled('ke_user_id')) {
//             Disposisi::create([
//                 'letter_id'      => $disposisi->letter_id,
//                 'parent_id'      => $disposisi->id,
//                 'dari_user_id'   => auth()->id(),
//                 'ke_user_id'     => $request->ke_user_id,
//                 'instruksi'      => $request->instruksi ?? 'Mohon tindaklanjuti',
//                 'prioritas'      => $request->prioritas ?? 'biasa',
//                 'status'         => 'pending',
//                 'deadline'       => $request->deadline ?? now()->addDays(3),
//             ]);
//         }

//         // 3. Sync status surat
//         $this->syncLetterStatus($disposisi->letter_id);

//         DB::commit();
//         return redirect()->back()->with('success', '✅ Disposisi berhasil diproses');

//     } catch (\Exception $e) {
//         DB::rollBack();
//         Log::error('Process Error: ' . $e->getMessage());
//         return redirect()->back()->with('error', '❌ Gagal: ' . $e->getMessage());
//     }
// }
/**
 * Process disposition (approve/forward/reject)
 */
public function process(Request $request, $id)
{
    // ========== DEBUG MODE START ==========
    $debug = [];
    $debug['timestamp'] = now()->toDateTimeString();
    $debug['url'] = $request->fullUrl();
    $debug['method'] = $request->method();
    $debug['disposisi_id'] = $id;
    $debug['user_id'] = auth()->id();
    $debug['request_all'] = $request->all();
    $debug['has_instruksi'] = $request->has('instruksi') ? 'YES' : 'NO';
    $debug['instruksi_value'] = $request->instruksi ?? 'NULL';
    $debug['instruksi_length'] = strlen($request->instruksi ?? '');
    
    echo "<div style='background:#f0f0f0; padding:20px; font-family:monospace; margin:20px; border:2px solid #333;'>";
    echo "<h2 style='color:#d9534f; margin:0 0 20px 0;'>🔍 DEBUG MODE - CATATAN TINDAK LANJUT</h2>";
    echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>📥 REQUEST DATA</h3>";
    echo "<pre>" . print_r($debug, true) . "</pre>";
    // ========== DEBUG MODE END ==========

    // Validasi lebih fleksibel
    $request->validate([
        'status'         => 'nullable|in:pending,diproses,selesai,ditolak,diteruskan,dibaca',
        'action'         => 'nullable|in:approve,forward,reject',
        'catatan_respon' => 'nullable|string',
        'instruksi'      => 'nullable|string|max:500',
        'ke_user_id'     => 'nullable|exists:users,id',
        'prioritas'      => 'nullable|in:biasa,penting,segera',
    ]);

    $disposisi = Disposisi::with('letter')->findOrFail($id);
    
    $debug['disposisi_before'] = [
        'id' => $disposisi->id,
        'status' => $disposisi->status,
        'instruksi_before' => $disposisi->instruksi,
        'letter_id' => $disposisi->letter_id,
    ];
    
    echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>📮 DISPOSISI BEFORE</h3>";
    echo "<pre>" . print_r($debug['disposisi_before'], true) . "</pre>";

    // Tentukan status dari action jika tidak dikirim
    $targetStatus = $request->status;
    
    if (!$targetStatus && $request->has('action')) {
        $action = $request->action;
        if ($action === 'approve' || $action === 'forward') {
            $targetStatus = 'diteruskan';
        } elseif ($action === 'reject') {
            $targetStatus = 'ditolak';
        } else {
            $targetStatus = 'diproses';
        }
    }

    DB::beginTransaction();
    try {
        // Data yang akan diupdate
        $updateData = [
            'status' => $targetStatus,
            'catatan_respon' => $request->catatan_respon ?? $disposisi->catatan_respon,
            'updated_at' => now(),
        ];
        
        // ✅ PENTING: Simpan catatan tindak lanjut ke field 'instruksi'
        if ($request->filled('instruksi')) {
            $updateData['instruksi'] = $request->instruksi;
            $debug['will_update_instruksi'] = true;
            $debug['new_instruksi_value'] = $request->instruksi;
        } else {
            $debug['will_update_instruksi'] = false;
            $debug['keep_old_instruksi'] = $disposisi->instruksi;
        }
        
        echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>✏️ DATA YANG AKAN DIUPDATE</h3>";
        echo "<pre>" . print_r($updateData, true) . "</pre>";

        // 1. Update disposisi saat ini
        $disposisi->update($updateData);
        
        $debug['disposisi_updated'] = true;
        $debug['update_data'] = $updateData;

        // 2. Jika forward ke user lain
        if ($targetStatus !== 'ditolak' && $request->filled('ke_user_id')) {
            echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>➡️ MEMBUAT DISPOSISI BARU</h3>";
            
            $newDisposisiData = [
                'letter_id'      => $disposisi->letter_id,
                'parent_id'      => $disposisi->id,
                'dari_user_id'   => auth()->id(),
                'ke_user_id'     => $request->ke_user_id,
                'instruksi'      => $request->instruksi ?? 'Mohon tindaklanjuti',
                'prioritas'      => $request->prioritas ?? 'biasa',
                'status'         => 'pending',
                'deadline'       => $request->deadline ?? now()->addDays(3),
            ];
            
            echo "<pre>" . print_r($newDisposisiData, true) . "</pre>";
            
            $newDisposisi = Disposisi::create($newDisposisiData);
            
            $debug['new_disposisi_created'] = true;
            $debug['new_disposisi_id'] = $newDisposisi->id;
            $debug['new_disposisi_instruksi'] = $newDisposisi->instruksi;
            $debug['forwarded_to_user_id'] = $request->ke_user_id;
            
            echo "<p style='background:#dff0d8; padding:10px; border-left:4px solid #3c763d;'>";
            echo "✅ <strong>Disposisi Baru Dibuat:</strong><br>";
            echo "ID: {$newDisposisi->id}<br>";
            echo "Kepada User ID: {$request->ke_user_id}<br>";
            echo "Instruksi: <strong>{$newDisposisi->instruksi}</strong>";
            echo "</p>";
        } else {
            $debug['new_disposisi_created'] = false;
        }

        // 3. Sync status surat
        echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>🔄 SYNCING LETTER STATUS</h3>";
        $this->syncLetterStatus($disposisi->letter_id);
        
        // Reload untuk debug
        $disposisi->refresh();
        $freshLetter = Letter::find($disposisi->letter_id);
        
        $debug['disposisi_after'] = [
            'id' => $disposisi->id,
            'status' => $disposisi->status,
            'instruksi_after' => $disposisi->instruksi,
        ];
        
        $debug['letter_after'] = $freshLetter->status;
        $debug['final_letter_status'] = $freshLetter ? ($freshLetter->status ?? 'NOT_FOUND') : 'NOT_FOUND';

        DB::commit();
        
        echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>✅ HASIL AKHIR</h3>";
        echo "<div style='background:#dff0d8; padding:15px; border:2px solid #3c763d;'>";
        echo "<p><strong>Disposisi #{$disposisi->id}:</strong></p>";
        echo "<ul>";
        echo "<li>Status: {$debug['disposisi_after']['status']}</li>";
        echo "<li>Instruksi: <strong>{$debug['disposisi_after']['instruksi_after']}</strong></li>";
        echo "</ul>";
        
        if ($debug['new_disposisi_created']) {
            echo "<p><strong>Disposisi Baru #{$debug['new_disposisi_id']}:</strong></p>";
            echo "<ul>";
            echo "<li>Diteruskan ke User ID: {$debug['forwarded_to_user_id']}</li>";
            echo "<li>Instruksi: <strong>{$debug['new_disposisi_instruksi']}</strong></li>";
            echo "</ul>";
        }
        
        echo "<p><strong>Letter Status:</strong> {$debug['final_letter_status']}</p>";
        echo "<p><strong>Transaction:</strong> <span style='color:green;'>COMMITTED</span></p>";
        echo "</div>";
        
        echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>📊 KESIMPULAN</h3>";
        echo "<div style='background:#fff3cd; padding:15px; border-left:4px solid #ffc107;'>";
        echo "<p><strong>Catatan Tindak Lanjut tersimpan di:</strong></p>";
        echo "<ol>";
        echo "<li>✅ Field <code>instruksi</code> pada disposisi saat ini (ID: {$disposisi->id})</li>";
        if ($debug['new_disposisi_created']) {
            echo "<li>✅ Field <code>instruksi</code> pada disposisi baru (ID: {$debug['new_disposisi_id']})</li>";
        }
        echo "</ol>";
        echo "<p><strong>Nilai yang tersimpan:</strong> <em>\"{$debug['disposisi_after']['instruksi_after']}\"</em></p>";
        echo "</div>";
        
        echo "<div style='margin-top:20px; padding:15px; background:#fff; border:2px solid #333;'>";
        echo "<a href='" . route('disposisi.inbox') . "' style='display:inline-block; padding:10px 20px; background:#0275d8; color:white; text-decoration:none; border-radius:4px;'>";
        echo "← Kembali ke Inbox";
        echo "</a>";
        echo "</div>";
        
        echo "</div>";
        exit; // STOP HERE

    } catch (\Exception $e) {
        DB::rollBack();
        $debug['error'] = $e->getMessage();
        
        echo "<h3 style='color:#d9534f; border-bottom:1px solid #ccc; padding-bottom:5px;'>❌ ERROR OCCURRED</h3>";
        echo "<div style='background:#f2dede; padding:15px; border:2px solid #d9534f;'>";
        echo "<p><strong>Error Message:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "</div>";
        echo "</div>";
        exit;
    }
}
/**
 * Sync status surat mengikuti disposisi aktif terkini
 * MENGGUNAKAN STATUS YANG ADA DI ENUM LETTERS
 */
private function syncLetterStatus($letterId)
{
    $letter = Letter::find($letterId);
    if (!$letter) {
        Log::error("❌ Letter #{$letterId} NOT FOUND");
        return;
    }

    // Ambil SEMUA disposisi untuk surat ini
    $allDisposisis = Disposisi::where('letter_id', $letterId)->get();

   // ✅ PRIORITAS 1: Cek apakah ada yang ditolak
    $hasRejected = $allDisposisis->contains('status', 'ditolak');

    if ($hasRejected) {
        $newStatus = 'ditolak';
    } else {
        // ✅ PRIORITAS 2: Cek apakah ada yang masih aktif
        $activeStatuses = ['pending', 'diproses', 'dibaca', 'diteruskan'];
        $hasActive = $allDisposisis->whereIn('status', $activeStatuses)->isNotEmpty();
        
        if ($hasActive) {
            // ✅ GUNAKAN 'diproses' (SUDAH ADA DI ENUM!)
            $newStatus = 'diproses';
        } else {
            // ✅ PRIORITAS 3: Semua sudah selesai
            // Pilih 'selesai' atau 'disetujui' sesuai kebutuhan
            $newStatus = 'selesai'; // atau 'disetujui'
        }
//         if ($hasActive) {
//     // ✅ GUNAKAN 'diproses' (bukan 'menunggu_verifikasi')
//     // Karena 'diproses' SUDAH ADA di ENUM tabel letters
//     $newStatus = 'diproses';
// } else {
//     // ✅ Gunakan 'disetujui' atau 'selesai' sesuai ENUM Anda
//     $newStatus = 'disetujui';
// }
    }
    // Update HANYA jika berbeda
    if ($letter->status !== $newStatus) {
        try {
            $letter->update(['status' => $newStatus]);
            Log::info("✅ Letter #{$letterId} updated to: {$newStatus}");
        } catch (\Exception $e) {
            Log::error("💥 Update failed: " . $e->getMessage());
        }
    }
}

    /**
     * Process disposition (approve/forward/reject)
     */
    /**
 * Process disposition (approve/forward/reject)
 */

/**
 * Sync status surat mengikuti disposisi aktif terkini
 */
/**
 * Debug version of syncLetterStatus
 */
private function syncLetterStatusDebug($letterId)
{
    $debug = [];
    $letter = Letter::find($letterId);
    
    if (!$letter) {
        $debug['error'] = "Letter not found";
        return $debug;
    }
    
    $debug['letter_current_status'] = $letter->status;
    $debug['letter_fillable'] = $letter->getFillable();
    
    // Ambil semua disposisi
    $allDisposisis = Disposisi::where('letter_id', $letterId)->get();
    
    $debug['total_disposisis'] = $allDisposisis->count();
    $debug['all_statuses'] = $allDisposisis->pluck('status')->unique()->toArray();
    
    // PRIORITAS 1: Cek ditolak
    $hasRejected = $allDisposisis->contains('status', 'ditolak');
    $debug['has_rejected'] = $hasRejected;
    
    if ($hasRejected) {
        $newStatus = 'ditolak';
        $debug['reason'] = 'Found rejected disposisi';
    } else {
        // PRIORITAS 2: Cek aktif
        $activeStatuses = ['pending', 'diproses', 'dibaca', 'diteruskan','selesai'];
        $activeDisposisis = $allDisposisis->whereIn('status', $activeStatuses);
        
        $debug['active_disposisis_count'] = $activeDisposisis->count();
        $debug['active_statuses'] = $activeDisposisis->pluck('status')->toArray();
        
        if ($activeDisposisis->isNotEmpty()) {
            $newStatus = 'diproses';
            $debug['reason'] = 'Has active disposisi';
        } else {
            // PRIORITAS 3: Semua selesai
            $newStatus = 'disetujui';
            $debug['reason'] = 'All disposisi finished';
        }
    }
    
    $debug['target_status'] = $newStatus;
    $debug['needs_update'] = ($letter->status !== $newStatus);
    
    if ($letter->status !== $newStatus) {
        try {
            $updated = $letter->update(['status' => $newStatus]);
            $debug['update_success'] = (bool)$updated;
            $debug['update_affected_rows'] = $updated;
        } catch (\Exception $e) {
            $debug['update_error'] = $e->getMessage();
        }
    }
    
    // Final check
    $freshLetter = Letter::find($letterId);
   $debug['final_letter_status'] = ($freshLetter && $freshLetter->status) ? $freshLetter->status : 'NOT_FOUND';
    
    return $debug;
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