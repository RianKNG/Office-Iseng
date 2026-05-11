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
        // 1. Format pesan default
        $pesan = $this->formatPesanDisposisi($user, $letter, $disp);
        
        // 2. Ambil mode notifikasi dari env
        $mode = env('NOTIF_MODE', 'log');
        $modes = $mode === 'all' ? ['database', 'whatsapp'] : [$mode];
        
        $success = false;
        
        foreach ($modes as $channel) {
            try {
                // ✅ GUNAKAN SWITCH, BUKAN MATCH
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
                        Log::warning("Channel notifikasi tidak dikenal: {$channel}");
                        break;
                }
                
                $success = true;
                
            } catch (Exception $e) {
                Log::error("✗ Notifikasi gagal via {$channel}: " . $e->getMessage(), [
                    'user_id' => $user->id,
                    'letter_id' => $letter->id,
                    'disposisi_id' => $disp->id,
                ]);
            }
        }
        
        return $success;
    }
    
    private function formatPesanDisposisi($user, $letter, $disp)
    {
        return "Surat Disposisi dari {$user->nama_lengkap}\n" .
               "Nomor: {$letter->nomor_surat}\n" .
               "Perihal: {$letter->perihal}";
    }
    
    private function sendViaLog($user, $letter, $disp, $pesan)
    {
        Log::info("✓ Notifikasi Log: {$pesan}");
    }
    
    private function sendViaDatabase($user, $letter, $disp, $pesan)
    {
        // Simpan ke tabel notifications
        $user->notifications()->create([
            'type' => 'disposisi',
            'data' => [
                'message' => $pesan,
                'letter_id' => $letter->id,
                'disposisi_id' => $disp->id,
            ],
            'read_at' => null,
        ]);
    }
    
    private function sendViaWhatsApp($user, $letter, $disp, $pesan)
    {
        // Implementasi WhatsApp (misal pakai Fonnte/Wablas)
        // $this->whatsappService->send($user->no_hp, $pesan);
        Log::info("WhatsApp ke {$user->nama_lengkap}: {$pesan}");
    }
    
    private function sendViaEmail($user, $letter, $disp, $pesan)
    {
        // Implementasi Email
        // Mail::to($user->email)->send(new DisposisiMail($letter, $disp));
        Log::info("Email ke {$user->email}: {$pesan}");
    }
}