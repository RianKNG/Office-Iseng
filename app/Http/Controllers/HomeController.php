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
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        // ==========================================
        // 📊 STATS CARD (Real Data)
        // ==========================================
        $stats = [
            
             'menunggu_verifikasi'=>1,
             'surat_masuk'  => Letter::where('jenis', 'masuk',$user->id)->count(),
            'surat_keluar'  => Letter::where('jenis', 'keluar',$user->id)->count(),
            'disposisi_terima' => Disposisi::where('ke_user_id', $user->id)->count(),
            'surat_disetujui' => Letter::where('created_by', $user->id)->where('status', 'disetujui')->count(),
            'surat_ditolak' => Letter::where('created_by', $user->id)->where('status', 'ditolak')->count(),
            'total_surat' => Letter::where('created_by', $user->id)->count(),
        ];
       
        // dd($stats);
        // ==========================================
        // 📈 DATA CHART - MANUAL/DUMMY (Bisa diganti query DB nanti)
        // ==========================================
        
        // Label bulan
        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Data manual: Surat Masuk per bulan (ganti dengan query DB nanti)
       // Mengambil data Surat Masuk per bulan secara otomatis
       
$chartSuratMasuk = [];
for ($m = 1; $m <= 12; $m++) {
    $chartSuratMasuk[] = Letter::where('created_by', $user->id)
        ->whereYear('created_at', date('Y'))
        ->whereMonth('created_at', $m)
        ->count();
}

// Mengambil data Disposisi per bulan secara otomatis
$chartDisposisi = [];
for ($m = 1; $m <= 12; $m++) {
    $chartDisposisi[] = Disposisi::where('ke_user_id', $user->id)
        ->whereYear('created_at', date('Y'))
        ->whereMonth('created_at', $m)
        ->count();
}
        
        // Data untuk Pie Chart: Status Disposisi
        // $statusDisposisi = [
        //     'pending' => 15,
        //     'diproses' => 28,
        //     'selesai' => 145,
        //     'ditolak' => 8
        // ];
         $statusDisposisi = [
                'pending'  => Disposisi::where('status', 'pending')->count(),
                'dibaca' => Disposisi::where('status', 'dibaca')->count(),
                'diproses'  => Disposisi::where('status', 'diproses')->count(),
                'diteruskan'  => Disposisi::where('status', 'diteruskan')->count(),
         ];
        //  dd( $statusDisposisi);
        // ==========================================
        // 📊 DATA KHUSUS ADMIN (Opsional)
        // ==========================================
        if ($user->isAdmin() || $user->isDirut()) {
            $stats['total_users'] = User::where('status', 'aktif')->count();
            $stats['total_surat_semua'] = Letter::count();
        }
        // dd($stats['total_surat_semua']);
        
        return view('home', compact(
            'stats',
            'bulanLabels',
            'chartSuratMasuk',
            'chartDisposisi',
            'statusDisposisi'
        ));
    }
}