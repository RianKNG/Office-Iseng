<?php

namespace App\Services;

use App\Models\User;
use App\Models\Letter;
use App\Models\Disposisi;
use Illuminate\Support\Facades\Log;
use Exception;

class NotifikasiService
{
    public function kirim(User $user, Letter $letter, Disposisi $disp)
    {
        // 🎯 LOG AWAL: Catat awal proses notifikasi
        Log::info("🔄 [NOTIF START] Proses notifikasi disposisi", [
            'disposisi_id' => $disp->id,
            'letter_id' => $letter->id,
            'dari_user' => [
                'id' => $user->id,
                'nama' => $user->nama_lengkap,
                'level' => $user->level,
                'jabatan' => $user->jabatan,
            ],
            'ke_user_id' => $disp->ke_user_id,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);

        // 1. Format pesan
        $pesan = $this->formatPesanDisposisi($user, $letter, $disp);
        
        // 2. Ambil mode notifikasi
        $mode = env('NOTIF_MODE', 'log');
        $modes = $mode === 'all' ? ['database', 'whatsapp'] : [$mode];
        
        $success = false;
        
        foreach ($modes as $channel) {
            try {
                Log::debug("→ Memproses channel: {$channel}");
                
                switch($channel) {
                    case 'log':
                        $this->sendViaLog($user, $letter, $disp, $pesan);
                        break;
                    case 'database':
                        $this->sendViaDatabase($user, $letter, $disp, $pesan);
                        break;
                    case 'whatsapp':
                        $this->sendViaWhatsApp($user, $letter, $disp, $pesan);
                        break;
                    case 'email':
                        $this->sendViaEmail($user, $letter, $disp, $pesan);
                        break;
                    default:
                        Log::warning("⚠ Channel tidak dikenal: {$channel}");
                        break;
                }
                
                $success = true;
                Log::debug("✓ Channel {$channel} selesai");
                
            } catch (Exception $e) {
                Log::error("✗ Notifikasi gagal via {$channel}: " . $e->getMessage(), [
                    'user_id' => $user->id,
                    'letter_id' => $letter->id,
                    'disposisi_id' => $disp->id,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        // 🎯 LOG AKHIR: Catat hasil proses
        Log::info("🏁 [NOTIF END] Proses notifikasi selesai", [
            'success' => $success,
            'channels_processed' => $modes,
            'disposisi_id' => $disp->id,
        ]);
        
        return $success;
    }
    
    private function formatPesanDisposisi($user, $letter, $disp)
    {
        $penerima = $disp->ke ?? null;
        
        return "📬 DISPOSISI SURAT\n" .
               "━━━━━━━━━━━━━━━━━━\n" .
               "👤 Dari: {$user->nama_lengkap} ({$user->jabatan})\n" .
               "📄 Nomor: {$letter->nomor_surat}\n" .
               "📝 Perihal: {$letter->perihal}\n" .
               "🎯 Kepada: " . ($penerima ? $penerima->nama_lengkap : 'Unknown') . "\n" .
               "💬 Instruksi: " . ($disp->instruksi ?? '-') . "\n" .
               "⏰ Waktu: " . now()->format('d/m/Y H:i');
    }
    
    private function sendViaLog($user, $letter, $disp, $pesan)
    {
        // 🎯 LOG KHUSUS: Detail alur "saling lempar"
        $penerima = $disp->ke ?? null;
        
        Log::channel('disposisi')->info("📤 DISPOSISI TERKIRIM", [
            'alur' => 'saling_lempar',
            'step' => $this->getStepNumber($disp),
            'dari' => [
                'id' => $user->id,
                'nama' => $user->nama_lengkap,
                'level' => $user->level,
                'struktur' => $user->struktur,
            ],
            'ke' => $penerima ? [
                'id' => $penerima->id,
                'nama' => $penerima->nama_lengkap,
                'level' => $penerima->level,
                'struktur' => $penerima->struktur,
            ] : null,
            'surat' => [
                'id' => $letter->id,
                'nomor' => $letter->nomor_surat,
                'perihal' => $letter->perihal,
                'status' => $letter->status,
            ],
            'disposisi' => [
                'id' => $disp->id,
                'parent_id' => $disp->parent_id,
                'instruksi' => $disp->instruksi,
                'prioritas' => $disp->prioritas,
                'status' => $disp->status,
            ],
            'pesan_formatted' => $pesan,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
        
        // Log singkat untuk easy reading di terminal
        Log::info("📤 [LOG] {$user->nama_lengkap} → {$penerima?->nama_lengkap}: {$letter->nomor_surat}");
    }
    
    private function sendViaDatabase($user, $letter, $disp, $pesan)
    {
        Log::debug("→ Menyimpan ke database notifications...");
        
        $notification = $user->notifications()->create([
            'type' => 'disposisi',
            'data' => [
                'message' => $pesan,
                'letter_id' => $letter->id,
                'disposisi_id' => $disp->id,
                'from_user' => [
                    'id' => $user->id,
                    'nama' => $user->nama_lengkap,
                    'jabatan' => $user->jabatan,
                ],
            ],
            'read_at' => null,
        ]);
        
        Log::debug("✓ Notifikasi database tersimpan (ID: {$notification->id})");
    }
    
    private function sendViaWhatsApp($user, $letter, $disp, $pesan)
    {
        // 🎯 LOG SIMULASI WA (belum integrasi real)
        $penerima = $disp->ke ?? null;
        
        Log::info("📱 [SIMULASI WA] Akan dikirim ke:", [
            'target_nama' => $penerima?->nama_lengkap,
            'target_no_hp' => $penerima?->no_hp ?? 'TIDAK ADA',
            'pesan_preview' => substr($pesan, 0, 100) . '...',
        ]);
        
        // Nanti saat integrasi real, ganti dengan:
        // $this->whatsappService->send($penerima->no_hp, $pesan);
    }
    
    private function sendViaEmail($user, $letter, $disp, $pesan)
    {
        Log::info("📧 [SIMULASI EMAIL] Akan dikirim ke: " . ($user->email ?? 'TIDAK ADA'));
    }
    
    // 🔍 Helper: Hitung step dalam alur disposisi
    private function getStepNumber($disp)
    {
        $step = 1;
        $current = $disp;
        
        while ($current->parent_id) {
            $step++;
            $current = Disposisi::find($current->parent_id);
            if (!$current) break;
        }
        
        return $step;
    }
}