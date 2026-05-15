<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Letter;
use App\Models\Disposisi;
use App\Models\User;

class HomeController extends Controller
{
    public function __construct()
    {
        // 🔹 BELAJAR ALUR: Middleware 'auth' wajib agar hanya user login yang bisa akses
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        // ==========================================
        // 📊 STATS CARD (Real Data)
        // ==========================================
        $stats = [
            // 🔹 BELAJAR ALUR: 'menunggu_verifikasi' hardcoded = 1 (mungkin placeholder)
            // Nanti bisa diganti query: Letter::where('created_by', $user->id)->where('status', 'menunggu_verifikasi')->count()
            'menunggu_verifikasi' => 1,
            
            // ✅ FIX: Tambahkan ->where('created_by', $user->id) terpisah
            // Sebelumnya: where('jenis', 'masuk', $user->id) ❌ SALAH SYNTAX
            'surat_masuk'  => Letter::where('jenis', 'masuk')
                                    ->where('created_by', $user->id)
                                    ->count(),
                                    
            'surat_keluar' => Letter::where('jenis', 'keluar')
                                    ->where('created_by', $user->id)
                                    ->count(),
            
            // ✅ Disposisi yang ditujukan ke user ini
            'disposisi_terima' => Disposisi::where('ke_user_id', $user->id)->count(),
            
            // ✅ Statistik surat yang dibuat user
            'surat_disetujui' => Letter::where('created_by', $user->id)
                                       ->where('status', 'disetujui')
                                       ->count(),
                                       
            'surat_ditolak' => Letter::where('created_by', $user->id)
                                     ->where('status', 'ditolak')
                                     ->count(),
                                     
            'total_surat' => Letter::where('created_by', $user->id)->count(),
        ];
       
        // ==========================================
        // 📈 DATA CHART - Line Chart (12 Bulan)
        // ==========================================
        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // 🔹 BELAJAR ALUR: Loop 1-12 untuk ambil data per bulan tahun ini
        // Untuk produksi besar, bisa dioptimasi dengan groupByMonth, tapi ini lebih mudah dipahami
        
        // Chart: Surat yang dibuat user per bulan
        $chartSuratMasuk = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartSuratMasuk[] = Letter::where('created_by', $user->id)
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', $m)
                ->count();
        }

        // Chart: Disposisi yang diterima user per bulan
        $chartDisposisi = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartDisposisi[] = Disposisi::where('ke_user_id', $user->id)
                ->whereYear('created_at', date('Y'))
                ->whereMonth('created_at', $m)
                ->count();
        }
        
        // ==========================================
        // 🥧 DATA PIE CHART: Status Disposisi
        // ==========================================
        // 🔹 BELAJAR ALUR: Filter by user agar non-admin tidak lihat data global
        $statusQuery = Disposisi::where('ke_user_id', $user->id);
        
        // Jika admin/dirut, boleh lihat semua disposisi sistem
        if ($user->isAdmin() || $user->isDirut()) {
            $statusQuery = Disposisi::query();
        }
        
        $statusDisposisi = [
            'pending'    => (clone $statusQuery)->where('status', 'pending')->count(),
            'dibaca'     => (clone $statusQuery)->where('status', 'dibaca')->count(),
            'diproses'   => (clone $statusQuery)->where('status', 'diproses')->count(),
            'diteruskan' => (clone $statusQuery)->where('status', 'diteruskan')->count(),
        ];
        
        // ==========================================
        // 👑 DATA KHUSUS ADMIN (Opsional)
        // ==========================================
        if ($user->isAdmin() || $user->isDirut()) {
            $stats['total_users'] = User::where('status', 'aktif')->count();
            $stats['total_surat_semua'] = Letter::count();
        }
        
        // 🔹 BELAJAR ALUR: compact() = cara singkat kirim variabel ke view
        return view('home', compact(
            'stats',
            'bulanLabels', 
            'chartSuratMasuk',
            'chartDisposisi',
            'statusDisposisi'
        ));
    }
}