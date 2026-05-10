<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Letter;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DisposisiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function inbox()
    {
        $disposisi = Disposisi::with(['letter', 'dari'])
            ->where('ke_user_id', auth()->id())
            ->latest()
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
    if ($request->has('letter_id')) {
        // --- LOGIKA BALASAN / TERUSKAN ---
        try {
            \App\Models\Disposisi::create([
                'letter_id'    => $request->letter_id,
                'parent_id'    => $request->parent_id,
                'dari_user_id' => auth()->id(),
                'ke_user_id'   => $request->ke_user_id,
                // Pastikan 'instruksi' menangkap name yang benar dari form (misal: instruksi_tambahan)
                'instruksi'    => $request->instruksi ?? $request->instruksi_tambahan,
                'balasan'        => $request->balasan, // Agar tersimpan di kolom balasan
                'prioritas'    => 'biasa',
                'status'       => 'pending',
                // Gunakan nama kolom yang benar sesuai migration Anda (misal: deadline atau created_at otomatis)
                'deadline'     => $request->deadline, 
            ]);

            return back()->with('success', 'Disposisi berhasil diteruskan!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }

    } else {
        // --- LOGIKA BUAT SURAT BARU ---
        $request->validate([
            'template_id' => 'required',
            'perihal'     => 'required',
            'ke_user_id'  => 'required'
        ]);

        \DB::beginTransaction();
        try {
            $letter = \App\Models\Letter::create([
                'template_id'   => $request->template_id,
                'nomor_surat'   => $request->nomor_surat,
                'tanggal'       => $request->tanggal,
                'perihal'       => $request->perihal,
                'jenis'         => 'keluar',
                'status'        => 'menunggu_verifikasi',
                'created_by'    => auth()->id(),
                'current_level' => 1,
            ]);

            \App\Models\Disposisi::create([
                'letter_id'    => $letter->id,
                'dari_user_id' => auth()->id(),
                'ke_user_id'   => $request->ke_user_id,
                'instruksi'    => 'Surat baru mohon ditindaklanjuti',
                'prioritas'    => 'biasa',
                'status'       => 'pending',
            ]);

            \DB::commit();
            return redirect()->route('letters.index')->with('success', 'Surat berhasil dibuat!');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
// Tambahkan ini di dalam DisposisiController.php

public function process(Request $request, $id)
{
 
//    $disposisi = \App\Models\Disposisi::findOrFail($id);
//     $action = $request->input('action');

//  if ($action === 'forward' || $action === 'teruskan') {
//     // Pastikan 'instruksi' adalah name yang ada di <textarea> Modal Anda
//     $isiPesan = $request->instruksi; 

//     // 1. UPDATE baris yang sedang aktif (ID 7)
//     $disposisi->update([
//         'status'         => 'diteruskan',
//         'instruksi'      => $isiPesan, // Mengisi "TAMBAHAN" ke kolom instruksi
//         'catatan_respon' => $isiPesan, // Mengisi "TAMBAHAN" ke kolom catatan_respon
//         'balasan'        => $isiPesan  // SEKARANG kolom balasan tidak akan NULL lagi
//     ]);
$disposisi = \App\Models\Disposisi::findOrFail($id);
    $action = $request->input('action');

    if ($action === 'forward' || $action === 'teruskan') {
        // Ambil teks dari textarea name="instruksi"
        $isiPesan = $request->instruksi; 

        $disposisi->update([
            'status'         => 'selesai',
            'instruksi'      => $isiPesan, // Update kolom instruksi
            'catatan_respon' => $isiPesan, // Update kolom catatan_respon
            'balasan'        => $isiPesan  // Update kolom balasan
        ]);
    
    // Refresh data agar dd() menunjukkan data terbaru dari DB
    dd($disposisi->fresh()->toArray());
        // 3. Gunakan untuk membuat baris baru
        \App\Models\Disposisi::create([
            'letter_id'    => $disposisi->letter_id,
            'parent_id'    => $disposisi->id,
            'dari_user_id' => auth()->id(),
            'ke_user_id'   => $request->ke_user_id,
            'instruksi'    => $newInstruksi, // Variabel sekarang sudah terdefinisi
            'prioritas'    => $disposisi->prioritas,
            'status'       => 'pending',
        ]);
      

        return back()->with('success', 'Disposisi berhasil diteruskan.');
    }
    
    // Logika untuk tombol 'Tandai Selesai'
    if ($action === 'selesai') {
        $disposisi->update([
            'status' => 'selesai',
            'catatan_respon' => $request->catatan_respon
        ]);
        return back()->with('success', 'Status diperbarui menjadi selesai.');
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