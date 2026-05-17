<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Letter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notification counts for current user
     */
    public function getNotifications()
    {
        $user = Auth::user();
        
        // Hitung disposisi baru (status pending/dibaca) untuk user ini
        $disposisiBaru = Disposisi::where('ke_user_id', $user->id)
            ->whereIn('status', ['pending', 'dibaca'])
            ->count();
        
        // Hitung surat masuk yang perlu diverifikasi (untuk Kabag/Kacab/Dirut)
        $suratPerluVerifikasi = 0;
        if (in_array($user->level, ['kabag', 'kacab', 'dirut', 'kasubag', 'kasie'])) {
            $suratPerluVerifikasi = Letter::where('status', 'menunggu_verifikasi')
                ->whereHas('disposisis', function($q) use ($user) {
                    $q->where('ke_user_id', $user->id)
                      ->where('status', 'pending');
                })
                ->count();
        }
        
        // Total notifikasi
        $totalNotifikasi = $disposisiBaru + $suratPerluVerifikasi;
        
        return response()->json([
            'disposisi_baru' => $disposisiBaru,
            'surat_verifikasi' => $suratPerluVerifikasi,
            'total' => $totalNotifikasi
        ]);
    }
    
    /**
     * Get detail notifikasi
     */
    public function getNotificationDetails()
    {
        $user = Auth::user();
        
        // Disposisi terbaru
        $disposisiList = Disposisi::with(['letter', 'dari'])
            ->where('ke_user_id', $user->id)
            ->whereIn('status', ['pending', 'dibaca'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'disposisi' => $disposisiList
        ]);
    }
}