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
    
    // ✅ TENTUKAN: Level mana yang hanya boleh lihat data sendiri?
    $restrictedLevels = ['staff', 'kasubag', 'kasie', 'kanit'];
    $isRestricted = in_array($user->level, $restrictedLevels);
    
    // ==========================================
    // 📊 STATS CARD (Smart Filter)
    // ==========================================
    
    // Base query builder
    $letterQuery = Letter::query();
    $disposisiQuery = Disposisi::query();
    
    // ✅ FILTER: User biasa hanya lihat milik sendiri
    if ($isRestricted) {
        $letterQuery->where('created_by', $user->id);
        $disposisiQuery->where('ke_user_id', $user->id);
    }
    // Admin/Dirut/Kabag/Kacab = lihat semua (tidak ada filter)
    
    $stats = [
        'menunggu_verifikasi' => (clone $letterQuery)->where('status', 'menunggu_verifikasi')->count(),
        
        'surat_masuk'  => (clone $letterQuery)->where('jenis', 'masuk')->count(),
        
        'surat_keluar' => (clone $letterQuery)->where('jenis', 'keluar')->count(),
        
        'disposisi_terima' => (clone $disposisiQuery)->count(),
        
        'surat_disetujui' => (clone $letterQuery)->where('status', 'disetujui')->count(),
        
        'surat_ditolak' => (clone $letterQuery)->where('status', 'ditolak')->count(),
        
        'total_surat' => (clone $letterQuery)->count(),
    ];
   
    // ==========================================
    // 📈 DATA CHART - Line Chart (12 Bulan)
    // ==========================================
    $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    
    // Chart: Surat per bulan
    $chartSuratMasuk = [];
    for ($m = 1; $m <= 12; $m++) {
        $query = (clone $letterQuery)
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', $m);
        
        $chartSuratMasuk[] = $query->count();
    }

    // Chart: Disposisi per bulan
    $chartDisposisi = [];
    for ($m = 1; $m <= 12; $m++) {
        $query = (clone $disposisiQuery)
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', $m);
        
        $chartDisposisi[] = $query->count();
    }
    
    // ==========================================
    // 🥧 DATA PIE CHART: Status Disposisi
    // ==========================================
    $statusDisposisi = [
        'pending'    => (clone $disposisiQuery)->where('status', 'pending')->count(),
        'dibaca'     => (clone $disposisiQuery)->where('status', 'dibaca')->count(),
        'diproses'   => (clone $disposisiQuery)->where('status', 'diproses')->count(),
        'diteruskan' => (clone $disposisiQuery)->where('status', 'diteruskan')->count(),
    ];
    
    // ==========================================
    // 👑 DATA KHUSUS ADMIN (Opsional)
    // ==========================================
    if ($user->isAdmin() || $user->isDirut()) {
        $stats['total_users'] = User::where('status', 'aktif')->count();
        $stats['total_surat_semua'] = Letter::count();
    }
    
    return view('home', compact(
        'stats',
        'bulanLabels', 
        'chartSuratMasuk',
        'chartDisposisi',
        'statusDisposisi'
    ));
}
}