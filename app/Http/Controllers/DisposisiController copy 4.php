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
    // ========== DEBUG MODE START ==========
    echo "<div style='background:#f0f0f0; padding:20px; font-family:monospace; margin:20px; border:2px solid #333;'>";
    echo "<h2 style='color:#d9534f; margin:0 0 20px 0;'>🔍 DEBUG MODE - DISPOSISI PROCESS</h2>";
    
    $debugInfo = [];
    $debugInfo['timestamp'] = now()->toDateTimeString();
    $debugInfo['url'] = $request->fullUrl();
    $debugInfo['method'] = $request->method();
    $debugInfo['disposisi_id'] = $id;
    $debugInfo['user_id'] = auth()->id();
    $debugInfo['request_input'] = $request->all();
    $debugInfo['has_status'] = $request->has('status') ? 'YES' : 'NO';
    $debugInfo['has_action'] = $request->has('action') ? 'YES' : 'NO';
    
    echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>📥 REQUEST INFO</h3>";
    echo "<pre>" . print_r($debugInfo, true) . "</pre>";

    // Validasi lebih fleksibel
    try {
        $request->validate([
            'status'         => 'nullable|in:pending,diproses,selesai,ditolak,diteruskan,dibaca',
            'action'         => 'nullable|in:approve,forward,reject',
            'catatan_respon' => 'nullable|string',
            'instruksi'      => 'nullable|string|max:500',
            'ke_user_id'     => 'nullable|exists:users,id',
            'prioritas'      => 'nullable|in:biasa,penting,segera',
        ]);
        echo "<p style='color:green;'>✅ <strong>Validation PASSED</strong></p>";
    } catch (\Exception $e) {
        echo "<p style='color:red;'>❌ <strong>Validation FAILED:</strong> " . $e->getMessage() . "</p>";
        echo "</div>";
        exit;
    }

    $disposisi = Disposisi::with('letter')->findOrFail($id);
    
    $debugInfo['disposisi_before'] = [
        'id' => $disposisi->id,
        'status' => $disposisi->status,
        'letter_id' => $disposisi->letter_id,
        'letter_status' => $disposisi->letter->status,
        'letter_nomor' => $disposisi->letter->nomor_surat,
    ];

    echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>📮 DISPOSISI BEFORE</h3>";
    echo "<pre>" . print_r($debugInfo['disposisi_before'], true) . "</pre>";

    DB::beginTransaction();
    try {
        // TENTUKAN STATUS
        $targetStatus = $request->status;
        
        if (!$targetStatus && $request->has('action')) {
            $action = $request->action;
            $statusMap = [
                'approve' => 'diteruskan',
                'forward' => 'diteruskan',
                'reject'  => 'ditolak',
            ];
            $targetStatus = $statusMap[$action] ?? 'pending';
            $debugInfo['auto_detected_status'] = $targetStatus;
        }
        
        if (!$targetStatus) {
            $targetStatus = 'diproses';
            $debugInfo['fallback_status'] = $targetStatus;
        }
        
        $debugInfo['target_status'] = $targetStatus;
        echo "<p style='background:#fff3cd; padding:10px; border-left:4px solid #ffc107;'>";
        echo "<strong>🎯 TARGET STATUS:</strong> <span style='font-size:1.2em; color:#856404;'>{$targetStatus}</span>";
        echo "</p>";

        // 1. Update disposisi
        echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>✏️ UPDATING DISPOSISI</h3>";
        $disposisi->update([
            'status'         => $targetStatus,
            'catatan_respon' => $request->catatan_respon ?? $disposisi->catatan_respon,
            'updated_at'     => now(),
        ]);
        echo "<p style='color:green;'>✅ Disposisi #{$disposisi->id} updated to: <strong>{$targetStatus}</strong></p>";

        // 2. Create new disposisi if forward
        if ($targetStatus !== 'ditolak' && $request->filled('ke_user_id')) {
            echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>➡️ CREATING NEW DISPOSISI</h3>";
            $newDisposisi = Disposisi::create([
                'letter_id'      => $disposisi->letter_id,
                'parent_id'      => $disposisi->id,
                'dari_user_id'   => auth()->id(),
                'ke_user_id'     => $request->ke_user_id,
                'instruksi'      => $request->instruksi ?? 'Mohon tindaklanjuti',
                'prioritas'      => $request->prioritas ?? 'biasa',
                'status'         => 'pending',
                'deadline'       => $request->deadline ?? now()->addDays(3),
            ]);
            echo "<p style='color:green;'>✅ New disposisi #{$newDisposisi->id} created for user {$request->ke_user_id}</p>";
        }

        // 3. Sync status surat
        echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>🔄 SYNCING LETTER STATUS</h3>";
        
        // Ambil semua disposisi
        $allDisposisis = Disposisi::where('letter_id', $disposisi->letter_id)->get();
        $debugInfo['all_disposisis'] = $allDisposisis->map(function($d) {
            return ['id' => $d->id, 'status' => $d->status, 'dari' => $d->dari->nama_lengkap, 'ke' => $d->ke->nama_lengkap];
        });
        
        echo "<p><strong>Total disposisi untuk surat ini:</strong> {$allDisposisis->count()}</p>";
        echo "<pre>" . print_r($debugInfo['all_disposisis'], true) . "</pre>";
        
        // Logika sync
        $hasRejected = $allDisposisis->contains('status', 'ditolak');
        $activeStatuses = ['pending', 'diproses', 'dibaca', 'diteruskan'];
        $hasActive = $allDisposisis->whereIn('status', $activeStatuses)->isNotEmpty();
        
        if ($hasRejected) {
            $newLetterStatus = 'ditolak';
            $reason = 'Found rejected disposisi';
        } elseif ($hasActive) {
            $newLetterStatus = 'diproses';
            $reason = 'Has active disposisi';
        } else {
            $newLetterStatus = 'selesai';
            $reason = 'All disposisi finished';
        }
        
        echo "<p><strong>Sync Logic:</strong></p>";
        echo "<ul>";
        echo "<li>Has Rejected: " . ($hasRejected ? 'YES' : 'NO') . "</li>";
        echo "<li>Has Active: " . ($hasActive ? 'YES' : 'NO') . "</li>";
        echo "<li>Reason: {$reason}</li>";
        echo "<li>Target Letter Status: <strong>{$newLetterStatus}</strong></li>";
        echo "</ul>";
        
        // Update letter
        $letter = Letter::find($disposisi->letter_id);
        $debugInfo['letter_before_sync'] = $letter->status;
        
        if ($letter->status !== $newLetterStatus) {
            echo "<p style='background:#d9edf7; padding:10px; border-left:4px solid #5bc0de;'>";
            echo "🔄 Updating letter #{$letter->id} status: ";
            echo "<del>{$letter->status}</del> → <strong>{$newLetterStatus}</strong>";
            echo "</p>";
            
            $updated = $letter->update(['status' => $newLetterStatus]);
            
            if ($updated) {
                echo "<p style='color:green;'>✅ Letter status updated successfully!</p>";
            } else {
                echo "<p style='color:red;'>❌ Letter update returned FALSE</p>";
            }
        } else {
            echo "<p style='color:gray;'>✓ No update needed - letter already has status: {$newLetterStatus}</p>";
        }

        DB::commit();
        
        // Reload untuk final check
        $disposisi->refresh();
        $freshLetter = Letter::find($disposisi->letter_id);
        
        $debugInfo['disposisi_after'] = $disposisi->status;
        $debugInfo['letter_after'] = $freshLetter->status;
        $debugInfo['final_letter_status'] = $freshLetter ? ($freshLetter->status ?? 'NOT_FOUND') : 'NOT_FOUND';
        
        echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>✅ FINAL RESULT</h3>";
        echo "<div style='background:#dff0d8; padding:15px; border:2px solid #3c763d;'>";
        echo "<p><strong>Disposisi #{$disposisi->id}:</strong> {$debugInfo['disposisi_after']}</p>";
        echo "<p><strong>Letter #{$freshLetter->id}:</strong> {$debugInfo['final_letter_status']}</p>";
        echo "<p><strong>Transaction:</strong> <span style='color:green;'>COMMITTED</span></p>";
        echo "</div>";
        
        echo "<h3 style='color:#0275d8; border-bottom:1px solid #ccc; padding-bottom:5px;'>📊 COMPLETE DEBUG ARRAY</h3>";
        echo "<pre>" . print_r($debugInfo, true) . "</pre>";
        
        echo "<div style='margin-top:20px; padding:15px; background:#fff; border:2px solid #333;'>";
        echo "<p style='margin:0 0 10px 0;'><strong>✅ Process completed successfully!</strong></p>";
        echo "<a href='" . route('disposisi.inbox') . "' style='display:inline-block; padding:10px 20px; background:#0275d8; color:white; text-decoration:none; border-radius:4px;'>";
        echo "← Kembali ke Inbox";
        echo "</a>";
        echo "</div>";
        
        echo "</div>";
        exit; // STOP HERE - JANGAN REDIRECT

    } catch (\Exception $e) {
        DB::rollBack();
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
 */
/**
 * Debug version of syncLetterStatus
 */
private function syncLetterStatus($letterId)
{
    $letter = Letter::find($letterId);
    if (!$letter) {
        Log::error("❌ Letter #{$letterId} NOT FOUND");
        return;
    }

    $allDisposisis = Disposisi::where('letter_id', $letterId)->get();

    // PRIORITAS 1: Cek ditolak
    if ($allDisposisis->contains('status', 'ditolak')) {
        $newStatus = 'ditolak';
    } 
    // PRIORITAS 2: Cek aktif - GUNAKAN 'menunggu_verifikasi' bukan 'diproses'
    elseif ($allDisposisis->whereIn('status', ['pending', 'diproses', 'dibaca', 'diteruskan'])->isNotEmpty()) {
        $newStatus = 'menunggu_verifikasi'; // ✅ GUNAKAN INI (sudah ada di ENUM)
    } 
    // PRIORITAS 3: Semua selesai - GUNAKAN 'disetujui' bukan 'selesai'
    else {
        $newStatus = 'disetujui'; // ✅ GUNAKAN INI (sudah ada di ENUM)
    }

    if ($letter->status !== $newStatus) {
        try {
            $letter->update(['status' => $newStatus]);
            Log::info("✅ Letter #{$letterId} updated to: {$newStatus}");
        } catch (\Exception $e) {
            Log::error("💥 Update failed: " . $e->getMessage());
        }
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