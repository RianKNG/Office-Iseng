<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Letter;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SebastianBergmann\Template\Template;

class DisposisiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function inbox()
    {
       
        $disposisi = Disposisi::with(['letter', 'dari', 'ke'])
            ->where('ke_user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('disposisi.inbox', compact('disposisi'));
    }
//     public function store(Request $request)
// {
//     // Menggunakan Transaction agar jika salah satu simpan gagal, data tidak kotor di database
//     \DB::beginTransaction();

//     try {
//         // 1. Simpan data utama ke tabel 'letters'
//         $letter = \App\Models\Letter::create([
//             'template_id'   => $request->template_id,
//             'nomor_surat'   => $request->nomor_surat,
//             'tanggal'       => $request->tanggal,
//             'perihal'       => $request->perihal,
//             'jenis'         => 'keluar',
//             'status'        => 'menunggu_verifikasi', // Menggunakan status yang valid di ENUM database
//             'created_by'    => auth()->id(),
//             'current_level' => 1,
//         ]);

//         // 2. Simpan detail field ke tabel 'letter_values'
//         if ($request->has('fields')) {
//             foreach ($request->fields as $field_id => $value) {
//                 \App\Models\LetterValue::create([
//                     'letter_id' => $letter->id,
//                     'field_id'  => $field_id,
//                     'value'     => $value
//                 ]);
//             }
//         }

//         // 3. Simpan data ke tabel 'disposisi' agar muncul di Inbox Penerima
//         // Menggunakan $request->penerima_id sesuai dengan hasil debug terakhir Anda
//         if ($request->penerima_id) {
//             \App\Models\Disposisi::create([
//                 'letter_id'     => $letter->id,
//                 'dari_user_id'  => auth()->id(),
//                 'ke_user_id'    => $request->penerima_id,
//                 'instruksi'     => 'Harap ditindaklanjuti',
//                 'prioritas'     => 'biasa',
//                 'status'        => 'pending',
//                 'tgl_disposisi' => now(),
//             ]);
//         }

//         \DB::commit();
//         return redirect()->route('letters.index')->with('success', 'Surat berhasil dikirim dan diteruskan!');

//     } catch (\Exception $e) {
//         DB::rollBack();
//         return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
//     }
// }
//     public function inbox() {
//     $userSekarang = auth()->user();
//     $semuaData = Disposisi::all();
    
//     return response()->json([
//         'id_user_login' => $userSekarang->id,
//         'nama_user_login' => $userSekarang->name,
//         'isi_tabel_disposisi' => $semuaData
//     ]);
// }

//     public function store(Request $request)
//     {
//         $request->validate([
//             'letter_id' => 'required|exists:letters,id',
//             'ke_user_id' => 'required|exists:users,id',
//             'instruksi' => 'required|string',
//             'prioritas' => 'required|in:biasa,penting,segera,rahasia',
//             'deadline' => 'nullable|date'
//         ]);

//         Disposisi::create([
//             'letter_id' => $request->letter_id,
//             'dari_user_id' => auth()->id(),
//             'ke_user_id' => $request->ke_user_id,
//             'instruksi' => $request->instruksi,
//             'prioritas' => $request->prioritas,
//             'status' => 'pending',
//             'deadline' => $request->deadline,
//         ]);

//         // Opsional: Update status surat jika disposisi pertama kali
//         $letter = Letter::find($request->letter_id);
//         if($letter->status == 'draft') {
//              // Logika approval bisa ditambahkan di sini tergantung bisnis proses
//              // Misal: Jika staff mengirim ke Kasubag, status jadi 'menunggu_verifikasi'
//              $nextLevel = auth()->user()->level_urutan + 1;
//              $letter->update([
//                  'status' => 'menunggu_verifikasi',
//                  'current_level' => $nextLevel
//              ]);
//         }

//         return back()->with('success', 'Disposisi berhasil dikirim.');
//     }
//     public function process(Request $request, $id)
// {
//     $disposisi = Disposisi::findOrFail($id);
//     $action = $request->input('action');

//     if ($action === 'forward') {
//         // Logika Forward
//         $instruksi = $request->input('instruksi_forward');
//         $ke_user = $request->input('ke_user_id');
//         // ... simpan ke database ...
//         return redirect()->back()->with('success', 'Berhasil diteruskan');
//     } 
    
//     if ($action === 'approve') {
//         // Logika Selesai
//         $catatan = $request->input('catatan');
//         $disposisi->update(['status' => 'selesai', 'catatan' => $catatan]);
//         return redirect()->back()->with('success', 'Disposisi diselesaikan');
//     }

//     if ($action === 'reject') {
//         // Logika Tolak
//         $disposisi->update(['status' => 'ditolak']);
//         return redirect()->back()->with('success', 'Disposisi dikembalikan');
//     }

//     // Jika sampai sini, berarti nilai 'action' tidak sesuai
//     return redirect()->back()->with('error', 'Aksi tidak valid: ' . $action);
// }
// public function store(Request $request)
// {
//     // 1. Ambil ID User yang sedang Login (Si pembuat surat)
//     $pengirim_id = auth()->id();
//     $nama_pengirim = auth()->user()->nama_lengkap;

//     // 2. Ambil ID Penerima dari Form
//     $penerima_id = $request->penerima_id; // Pastikan name di input HTML adalah 'penerima_id'
    
//     // Cari data penerima di database untuk memastikan ID itu ada orangnya
//     $penerima = \App\Models\User::find($penerima_id);

//     // 3. MODE DEBUG: Berhenti di sini dan tampilkan data
//     dd([
//         'KETERANGAN' => 'Mengecek apakah ID Pengirim dan Penerima sudah benar',
//         'SAYA_LOGIN_SEBAGAI' => [
//             'id' => $pengirim_id,
//             'nama' => $nama_pengirim,
//             'jabatan' => auth()->user()->jabatan
//         ],
//         'SAYA_MENGIRIM_KE' => [
//             'id' => $penerima_id,
//             'nama' => $penerima ? $penerima->nama_lengkap : 'ID TIDAK DITEMUKAN DI DATABASE!',
//             'jabatan' => $penerima ? $penerima->jabatan : 'N/A'
//         ],
//         'DATA_FORM_LAINNYA' => $request->except(['_token'])
//     ]);

//     // Kode di bawah ini tidak akan jalan selama ada dd() di atas
//     // $letter = Letter::create([...]);
// }
// public function store(Request $request)
// {
//     // 1. Ambil ID Penerima sesuai hasil debug (ke_user_id)
//     $penerima_id = $request->ke_user_id; 
    
//     // 2. Ambil ID Surat yang sedang dibalas (letter_id)
//     $letter_id = $request->letter_id;

//     // 3. LOGIKA: Jika ini adalah BALASAN, kita TIDAK membuat Letter baru, 
//     // tapi membuat DISPOSISI baru untuk surat yang sudah ada.
//     if ($letter_id) {
//         \App\Models\Disposisi::create([
//             'letter_id'     => $letter_id,
//             'dari_user_id'  => auth()->id(),
//             'ke_user_id'    => $penerima_id,
//             'parent_id'     => $request->parent_id, // Untuk melacak silsilah disposisi
//             'instruksi'     => $request->instruksi,
//             'prioritas'     => 'biasa',
//             'status'        => 'pending',
//             'tgl_disposisi' => now(),
//         ]);

//         return back()->with('success', 'Balasan berhasil dikirim!');
//     }

//     // Jika ini bukan balasan (buat surat baru), baru jalankan Letter::create...
// }

public function store(Request $request)
{
    // Debug: Log request data (hapus setelah berhasil)
    \Log::info('=== STORE LETTER DEBUG ===');
    \Log::info('All Request:', $request->all());
    \Log::info('Fields:', $request->fields ?? []);
    \Log::info('ke_user_id:', $request->ke_user_id);

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
        
        // Handle file upload
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

        // 3. Buat Disposisi Awal (Workflow)
        Disposisi::create([
            'letter_id'      => $letter->id,
            'dari_user_id'   => auth()->id(),
            'ke_user_id'     => $request->ke_user_id,
            'instruksi'      => 'Surat baru - mohon ditindaklanjuti',
            'prioritas'      => 'biasa',
            'status'         => 'pending',
            'deadline'       => now()->addDays(3),
        ]);

        DB::commit();
        
        // Debug success
        \Log::info('=== STORE SUCCESS ===', [
            'letter_id' => $letter->id,
            'ke_user_id' => $request->ke_user_id
        ]);
        
        return redirect()->route('letters.index')
            ->with('success', '✅ Surat berhasil dibuat dan diteruskan.');

    } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::error('=== STORE ERROR ===', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->except(['password', 'file_path'])
        ]);
        
        return back()
            ->withInput()
            ->with('error', '❌ Gagal: ' . $e->getMessage());
    }
}
public function process(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,diproses,selesai,ditolak',
        'catatan_respon' => 'nullable|string',
        'instruksi' => 'nullable|string|max:500',
        'ke_user_id' => 'nullable|exists:users,id', // Untuk forward
        'prioritas' => 'nullable|in:biasa,penting,segera',
    ]);

    $disposisi = Disposisi::with('letter')->findOrFail($id);

    DB::beginTransaction();
    try {
        // 1. Update disposisi saat ini
        $disposisi->update([
            'status' => $request->status,
            'catatan_respon' => $request->catatan_respon ?? $disposisi->catatan_respon,
            'updated_at' => now(),
        ]);

        // 2. Jika ada forward/disposisi lanjutan ke user lain
        if ($request->status !== 'ditolak' && $request->filled('ke_user_id')) {
            Disposisi::create([
                'letter_id'      => $disposisi->letter_id,
                'parent_id'      => $disposisi->id,
                'dari_user_id'   => auth()->id(),
                'ke_user_id'     => $request->ke_user_id,
                'instruksi'      => $request->instruksi ?? 'Mohon tindaklanjuti',
                'prioritas'      => $request->prioritas ?? 'biasa',
                'status'         => 'pending',
                'deadline'       => $request->deadline ?? now()->addDays(3),
            ]);
        }

        // 3. ✅ PENTING: Update status letter mengikuti alur disposisi
        $this->updateLetterStatusBerjenjang($disposisi->letter);

        DB::commit();
        
        return redirect()->back()->with('success', '✅ Disposisi berhasil diproses');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Process Disposisi Error: ' . $e->getMessage());
        return redirect()->back()->with('error', '❌ Gagal: ' . $e->getMessage());
    }
}

/**
 * ✅ FUNGSI BARU: Update status letter mengikuti alur disposisi berjenjang
 */
private function updateLetterStatusBerjenjang($letter)
{
    // Ambil SEMUA disposisi untuk letter ini
    $allDisposisis = $letter->disposisis;
    
    // Hitung status
    $pendingCount  = $allDisposisis->where('status', 'pending')->count();
    $diprosesCount = $allDisposisis->where('status', 'diproses')->count();
    $selesaiCount  = $allDisposisis->where('status', 'selesai')->count();
    $ditolakCount  = $allDisposisis->where('status', 'ditolak')->count();
    
    // Cek disposisi terakhir (yang paling baru)
    $latestDisposisi = $allDisposisis->sortByDesc('created_at')->first();
    
    $newStatus = 'menunggu_verifikasi'; // Default
    
    // 🎯 LOGIKA STATUS BERJENJANG:
    
    if ($ditolakCount > 0) {
        // Ada yang ditolak = surat ditolak
        $newStatus = 'ditolak';
        
    } elseif ($latestDisposisi && $latestDisposisi->status === 'selesai') {
        // Disposisi terakhir sudah selesai
        // Cek apakah ada disposisi child (lanjutan) yang masih pending
        $hasChildPending = $allDisposisis->where('parent_id', $latestDisposisi->id)
            ->where('status', 'pending')
            ->count() > 0;
        
        if ($hasChildPending) {
            // Ada lanjutan yang masih pending
            $newStatus = 'dalam_proses';
        } else {
            // Tidak ada lanjutan = benar-benar selesai
            $newStatus = 'selesai';
        }
        
    } elseif ($pendingCount > 0 && $diprosesCount === 0 && $selesaiCount === 0) {
        // Masih di awal, semua masih pending
        $newStatus = 'menunggu_verifikasi';
        
    } elseif ($diprosesCount > 0 || ($selesaiCount > 0 && $pendingCount > 0)) {
        // Ada yang sedang diproses ATAU 
        // Ada yang selesai tapi masih ada yang pending (berjenjang)
        $newStatus = 'dalam_proses';
        
    } elseif ($selesaiCount > 0 && $pendingCount === 0 && $diprosesCount === 0) {
        // Semua disposisi selesai
        $newStatus = 'selesai';
    }
    
    // Update status letter jika berubah
    if ($letter->status !== $newStatus) {
        $letter->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);
        
        \Log::info("Letter #{$letter->id} status updated: {$newStatus}", [
            'pending' => $pendingCount,
            'diproses' => $diprosesCount,
            'selesai' => $selesaiCount,
            'ditolak' => $ditolakCount,
            'latest_status' => $latestDisposisi ? $latestDisposisi->status : null,
            //'latest_status' => $latestDisposisi->status ?? null
        ]);
    }
}
/**
 * ✅ FUNGSI BARU: Update status letter berdasarkan disposisi
 */
private function updateLetterStatus($letter)
{
    // Cek semua disposisi untuk letter ini
    $disposisis = $letter->disposisis;
    
    // Hitung status
    $pendingCount = $disposisis->where('status', 'pending')->count();
    $diprosesCount = $disposisis->where('status', 'diproses')->count();
    $selesaiCount = $disposisis->where('status', 'selesai')->count();
    $ditolakCount = $disposisis->where('status', 'ditolak')->count();
    
    // Logika update status letter
    if ($ditolakCount > 0) {
        // Ada yang ditolak = letter ditolak
        $newStatus = 'ditolak';
    } elseif ($pendingCount > 0 || $diprosesCount > 0) {
        // Masih ada yang pending/diproses = masih menunggu
        $newStatus = 'menunggu_verifikasi';
    } elseif ($selesaiCount > 0 && $pendingCount === 0 && $diprosesCount === 0) {
        // Semua selesai = letter selesai
        $newStatus = 'selesai';
    } else {
        // Default
        $newStatus = 'menunggu_verifikasi';
    }
    
    // Update jika berbeda
    if ($letter->status !== $newStatus) {
        $letter->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);
        
        \Log::info("Letter #{$letter->id} status updated to: {$newStatus}", [
            'pending' => $pendingCount,
            'diproses' => $diprosesCount,
            'selesai' => $selesaiCount,
            'ditolak' => $ditolakCount,
        ]);
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

    // Cek otorisasi - hanya pengirim atau penerima yang bisa lihat
    if ($disposisi->ke_user_id != auth()->id() && $disposisi->dari_user_id != auth()->id()) {
        abort(403, 'Anda tidak memiliki akses ke disposisi ini');
    }

    // Update status jadi 'dibaca' jika masih pending dan yang lihat adalah penerima
    if ($disposisi->status == 'pending' && $disposisi->ke_user_id == auth()->id()) {
        $disposisi->update(['status' => 'dibaca']);
    }

    return view('disposisi.show', compact('disposisi'));
}

}