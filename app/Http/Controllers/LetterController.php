<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Letter;
use App\Models\LetterValue;
use App\Models\Template;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LetterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
{
    $templates = Template::where('is_active', true)
        ->orderBy('jenis')
        ->orderBy('nama_template')
        ->get();
    
    // ✅ FIX: Query cabangs TANPA is_active
    $cabangs = \App\Models\Cabang::orderBy('tipe')
        ->orderBy('nama_cabang')
        ->get();
    
    // ✅ Load users untuk dropdown penerima disposisi
    $users = auth()->user()->getAvailableForwardTargets()->load('cabang');
    
    // ✅ Format data untuk JavaScript
    $usersData = $users->map(function($u) {
        return [
            'id' => $u->id,
            'nama_lengkap' => $u->nama_lengkap,
            'jabatan' => $u->jabatan,
            'cabang_nama' => $u->cabang ? $u->cabang->nama_cabang : ($u->isPusat() ? 'Kantor Pusat' : ''),
            'level_label' => $u->getLevelLabel(),
            'struktur_label' => $u->getStrukturLabel(),
        ];
    });
    
    return view('letters.create', compact('templates', 'usersData', 'cabangs'));
}
    
    

    public function store(Request $request)
{
    Log::info('=== STORE LETTER ===', [
        'user_id' => auth()->id(),
        'request' => $request->all()
    ]);

    // ✅ Validasi conditional: ke_user_id hanya wajib untuk template dengan disposisi
     // ✅ CHECK: Apakah template ini butuh penerima?
    $template = Template::find($request->template_id);
    $requiresRecipient = in_array(strtolower($template->kode_template), ['sk-resmi', 'nd-int']);
    
    $rules = [
        'template_id' => 'required|exists:templates,id',
        'nomor_surat' => 'required|string|max:100',
        'tanggal'     => 'required|date',
        'perihal'     => 'required|string|max:255',
        'fields.*'    => 'nullable|string|max:1000',
        'file_path'   => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
    ];
    
    // ✅ Hanya wajibkan ke_user_id jika template memerlukannya
    if ($requiresRecipient) {
        $rules['ke_user_id'] = 'required|exists:users,id';
    }
    $request->validate($rules);

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
            // ✅ Hanya set ke_user_id jika ada
            'ke_user_id'    => $request->filled('ke_user_id') ? $request->ke_user_id : null,
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

        // 3. Buat Disposisi (hanya jika ada ke_user_id)
        if ($request->filled('ke_user_id')) {
            $sender = auth()->user();
            $target = User::find($request->ke_user_id);
            
            if ($target && !$sender->canForwardTo($target)) {
                DB::rollBack();
                return back()->withInput()->with('error', 
                    '❌ Anda tidak dapat meneruskan surat ke user ini (beda struktur/unit).');
            }

            Disposisi::create([
                'letter_id'      => $letter->id,
                'dari_user_id'   => auth()->id(),
                'ke_user_id'     => $request->ke_user_id,
                'instruksi'      => 'Surat baru - mohon ditindaklanjuti',
                'prioritas'      => 'biasa',
                'status'         => 'pending',
                'deadline'       => now()->addDays(3),
            ]);
        }

        DB::commit();
        Log::info('Letter created successfully', ['letter_id' => $letter->id]);
        
        return redirect()->route('letters.index')
            ->with('success', '✅ Surat berhasil dibuat dan diteruskan.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Store Letter Failed: ' . $e->getMessage());
        return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
    }
}
    // API Endpoint untuk AJAX
    public function getFields($templateId)
    {
        $template = Template::with('fields')->findOrFail($templateId);
        return response()->json($template->fields);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // ✅ Load relasi penerima untuk ditampilkan di view
        $query = Letter::with(array('template', 'creator', 'penerima'));

        // ✅ FILTER: Staff, Kasubag, Kasie, Kanit hanya lihat surat yang dibuat sendiri
        $restrictedLevels = array('staff', 'kasubag', 'kasie', 'kanit');
        
        if (in_array($user->level, $restrictedLevels)) {
            $query->where('created_by', $user->id);
        }

        // Filter Logic
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nomor_surat', 'like', '%'.$request->search.'%')
                  ->orWhere('perihal', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('jenis')) $query->where('jenis', $request->jenis);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('from_date')) $query->whereDate('tanggal', '>=', $request->from_date);
        // Setelah filter status, tambah:
        if ($request->filled('struktur')) {
            // 🔹 BELAJAR: Filter via relasi cabang->tipe
            $query->whereHas('penerima', function($q) use ($request) {
                $q->whereHas('cabang', function($c) use ($request) {
                    $c->where('tipe', $request->struktur);
                });
            });
        }

        $letters = $query->latest()->paginate(10);

        // ✅ Hitung statistik dengan status yang benar
// ✅ Hitung statistik dengan status yang benar
$stats = array(
    'total' => Letter::when(in_array($user->level, $restrictedLevels), function($q) use ($user) {
        return $q->where('created_by', $user->id);
    })->count(),
    'waiting' => Letter::where('status', 'menunggu_verifikasi')
        ->when(in_array($user->level, $restrictedLevels), function($q) use ($user) {
            return $q->where('created_by', $user->id);
        })->count(),
    // ✅ FIX: Hitung KEDUA status
    'approved' => Letter::whereIn('status', ['disetujui', 'selesai'])  // ← UBAH DISINI
        ->when(in_array($user->level, $restrictedLevels), function($q) use ($user) {
            return $q->where('created_by', $user->id);
        })->count(),
    'rejected' => Letter::where('status', 'ditolak')
        ->when(in_array($user->level, $restrictedLevels), function($q) use ($user) {
            return $q->where('created_by', $user->id);
        })->count(),
);
        // 🔹 AJAX Response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(array(
                'tableHtml' => view('letters._table_rows', compact('letters'))->render(),
                'pagination' => $letters->withQueryString()->links()->toHtml(),
                'count' => $letters->count(),
                'stats' => $stats
            ));
        }

        return view('letters.index', compact('letters', 'stats'));
    }
    
    public function show($id)
    {
        // ✅ Load relasi penerima di show
        $letter = Letter::with(array('template.fields', 'values.field', 'disposisis.dari', 'disposisis.ke', 'creator', 'penerima'))->findOrFail($id);

        $signatureBase64 = null;
        if ($letter->creator && $letter->creator->signature) {
            $cleanPath = ltrim(trim($letter->creator->signature), '/');
            $path = storage_path('app/public/' . $cleanPath);
            $fullPath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
            
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                $base64 = base64_encode($data);
                $cleanBase64 = str_replace(array("\r", "\n"), '', $base64);
                $signatureBase64 = 'data:image/' . $type . ';base64,' . $cleanBase64;
            }
        }

        return view('letters.show', compact('letter', 'signatureBase64'));
    }

    public function generateNomorSurat(Request $request)
    {
        $kode = $request->input('kode');
        if (!$kode) {
            return response()->json(array('error' => 'Kode template tidak ditemukan'), 400);
        }

        $bulan = date('n');
        $tahun = date('Y');
        
        $lastLetter = Letter::whereYear('created_at', $tahun)
            ->whereHas('template', function($query) use ($kode) {
                $query->where('kode_template', $kode);
            })
            ->orderBy('id', 'desc')
            ->first();
        
        $nomorUrut = 1;

        if ($lastLetter && !empty($lastLetter->nomor_surat)) {
            $parts = explode('/', $lastLetter->nomor_surat);
            if (count($parts) > 0 && is_numeric($parts[0])) {
                $nomorUrut = intval($parts[0]) + 1;
            }
        }
        
        $bulanRomawi = array('I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII');
        $nomorSurat = sprintf('%03d', $nomorUrut) . '/' . $kode . '/' . $bulanRomawi[$bulan - 1] . '/' . $tahun;
        
        return response()->json(array(
            'success' => true,
            'nomor' => $nomorSurat
        ));
    }

    public function masuk(Request $request)
    {
        $user = auth()->user();
        
        // ✅ HARD FILTER: jenis = 'masuk' (tidak bisa di-override)
        $query = Letter::with(array('template', 'creator', 'penerima'))
            ->where('jenis', 'masuk');

        // Search - TETAP pertahankan filter jenis
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nomor_surat', 'like', '%'.$request->search.'%')
                  ->orWhere('perihal', 'like', '%'.$request->search.'%');
            });
        }

        // Filter lain tetap boleh
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('tanggal', '>=', $request->from_date);
        }

        // ✅ FILTER: Staff, Kasubag, Kasie, Kanit hanya lihat surat sendiri
        $restrictedLevels = array('staff', 'kasubag', 'kasie', 'kanit');
        if (in_array($user->level, $restrictedLevels)) {
            $query->where('created_by', $user->id);
        }

        $letters = $query->latest()->paginate(10);

        // ✅ Statistik - filter level juga di stats (PHP 7.4 compatible)
        $statsQuery = Letter::where('jenis', 'masuk')
            ->when(in_array($user->level, $restrictedLevels), function($q) use ($user) {
                return $q->where('created_by', $user->id);
            });
        
        $statsData = (clone $statsQuery)
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN status = "menunggu_verifikasi" THEN 1 END) as waiting,
                COUNT(CASE WHEN status = "disetujui" THEN 1 END) as approved,
                COUNT(CASE WHEN status = "ditolak" THEN 1 END) as rejected
            ')->first();
        
        $stats = array(
            'total'    => $statsData->total,
            'waiting'  => $statsData->waiting,
            'approved' => $statsData->approved,
            'rejected' => $statsData->rejected,
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(array(
                'tableHtml' => view('letters._table_rows', compact('letters'))->render(),
                'pagination' => $letters->withQueryString()->links()->toHtml(),
                'count' => $letters->count(),
                'stats' => $stats
            ));
        }

        return view('letters.index', compact('letters', 'stats'));
    }
    // ==========================================
// ✅ METHOD: SURAT KELUAR (jenis = 'keluar')
// ==========================================
public function keluar(Request $request)
{
    $user = auth()->user();
    
    // ✅ HARD FILTER: hanya surat keluar
    $query = Letter::with(array('template', 'creator', 'penerima'))
        ->where('jenis', 'keluar');

    // ✅ FILTER LEVEL: staff/kasubag/kasie/kanit hanya lihat surat sendiri
    $restrictedLevels = array('staff', 'kasubag', 'kasie', 'kanit');
    if (in_array($user->level, $restrictedLevels)) {
        $query->where('created_by', $user->id);
    }

    // 🔍 Filter Search
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('nomor_surat', 'like', '%'.$request->search.'%')
              ->orWhere('perihal', 'like', '%'.$request->search.'%');
        });
    }

    // 🔍 Filter Status & Tanggal
    if ($request->filled('status')) $query->where('status', $request->status);
    if ($request->filled('from_date')) $query->whereDate('tanggal', '>=', $request->from_date);

    $letters = $query->latest()->paginate(10);

    // ✅ Statistik khusus surat keluar
    $statsQuery = Letter::where('jenis', 'keluar')
        ->when(in_array($user->level, $restrictedLevels), function($q) use ($user) {
            return $q->where('created_by', $user->id);
        });
    
    $statsData = (clone $statsQuery)
        ->selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN status = "menunggu_verifikasi" THEN 1 END) as waiting,
            COUNT(CASE WHEN status = "disetujui" THEN 1 END) as approved,
            COUNT(CASE WHEN status = "ditolak" THEN 1 END) as rejected
        ')->first();
    
    $stats = array(
        'total'    => $statsData->total,
        'waiting'  => $statsData->waiting,
        'approved' => $statsData->approved,
        'rejected' => $statsData->rejected,
    );

    // ✅ AJAX Response
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json(array(
            'tableHtml' => view('letters._table_rows', compact('letters'))->render(),
            'pagination' => $letters->withQueryString()->links()->toHtml(),
            'count' => $letters->count(),
            'stats' => $stats
        ));
    }

    return view('letters.index', compact('letters', 'stats'));
}

// ==========================================
// ✅ METHOD: NOTA DINAS (jenis = 'nota')
// ==========================================
public function nota(Request $request)
{
    $user = auth()->user();
    
    // ✅ HARD FILTER: hanya nota dinas
    $query = Letter::with(array('template', 'creator', 'penerima'))
        ->where('jenis', 'nota');

    $restrictedLevels = array('staff', 'kasubag', 'kasie', 'kanit');
    if (in_array($user->level, $restrictedLevels)) {
        $query->where('created_by', $user->id);
    }

    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('nomor_surat', 'like', '%'.$request->search.'%')
              ->orWhere('perihal', 'like', '%'.$request->search.'%');
        });
    }
    if ($request->filled('status')) $query->where('status', $request->status);
    if ($request->filled('from_date')) $query->whereDate('tanggal', '>=', $request->from_date);

    $letters = $query->latest()->paginate(10);

    // Statistik khusus nota dinas
    $statsQuery = Letter::where('jenis', 'nota')
        ->when(in_array($user->level, $restrictedLevels), function($q) use ($user) {
            return $q->where('created_by', $user->id);
        });
    
    $statsData = (clone $statsQuery)
        ->selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN status = "menunggu_verifikasi" THEN 1 END) as waiting,
            COUNT(CASE WHEN status = "disetujui" THEN 1 END) as approved,
            COUNT(CASE WHEN status = "ditolak" THEN 1 END) as rejected
        ')->first();
    
    $stats = array(
        'total'    => $statsData->total,
        'waiting'  => $statsData->waiting,
        'approved' => $statsData->approved,
        'rejected' => $statsData->rejected,
    );

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json(array(
            'tableHtml' => view('letters._table_rows', compact('letters'))->render(),
            'pagination' => $letters->withQueryString()->links()->toHtml(),
            'count' => $letters->count(),
            'stats' => $stats
        ));
    }

    return view('letters.index', compact('letters', 'stats'));
}
    

    // public function downloadPdf($id)
    // {
    //     $letter = Letter::with(array('creator', 'values.field', 'penerima'))->findOrFail($id);
        
    //     $signatureBase64 = null;
        
    //     if ($letter->creator && $letter->creator->signature) {
    //         $fileName = trim($letter->creator->signature); 
    //         $path = storage_path('app/public/signatures/' . $fileName);
    //         $fullPath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
            
    //         if (file_exists($fullPath)) {
    //             $type = pathinfo($fullPath, PATHINFO_EXTENSION);
    //             $data = file_get_contents($fullPath);
    //             $signatureBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    //         }
    //     }

    //     $pdf = Pdf::loadView('letters.pdf', compact('letter', 'signatureBase64'))
    //               ->setOption(array(
    //                   'isRemoteEnabled' => true,
    //                   'isHtml5ParserEnabled' => true,
    //                   'chroot' => storage_path('app/public'),
    //               ))
    //               ->setPaper('a4', 'portrait');

    //     return $pdf->stream('Surat-' . $letter->nomor_surat . '.pdf');
    // }
//     public function downloadPdf($id)
// {
//     $letter = Letter::with(array('creator', 'approver', 'values.field', 'penerima'))->findOrFail($id);
    
//     // ✅ Cek status untuk conditional TTD
//     $is_approved = in_array($letter->status, array('disetujui', 'selesai', 'arsip'));
//     $generated_at = date('d F Y');
    
//     // ✅ Generate Signature Kabag
//     $signatureKabag = null;
//     if ($letter->creator && $letter->creator->signature) {
//         $fileName = trim($letter->creator->signature);
//         $path = storage_path('app/public/signatures/' . $fileName);
//         if (file_exists($path)) {
//             $type = pathinfo($path, PATHINFO_EXTENSION);
//             $data = file_get_contents($path);
//             $signatureKabag = 'data:image/' . $type . ';base64,' . base64_encode($data);
//         }
//     }
    
//     // ✅ Generate Signature Dirut (hanya jika approved)
//     $signatureDirut = null;
//     if ($is_approved && $letter->approver && $letter->approver->signature) {
//         $fileName = trim($letter->approver->signature);
//         $path = storage_path('app/public/signatures/' . $fileName);
//         if (file_exists($path)) {
//             $type = pathinfo($path, PATHINFO_EXTENSION);
//             $data = file_get_contents($path);
//             $signatureDirut = 'data:image/' . $type . ';base64,' . base64_encode($data);
//         }
//     }
    
//     $pdf = Pdf::loadView('letters.pdf', compact(
//         'letter', 'signatureKabag', 'signatureDirut', 'is_approved', 'generated_at'
//     ))
//     ->setOption(array('isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true))
//     ->setPaper('a4', 'portrait');
    
//     return $pdf->stream('Surat-' . $letter->nomor_surat . '.pdf');
// }
// public function downloadPdf($id)
// {
//     // 1. Load relasi
//     $letter = Letter::with(array('creator', 'approver', 'values.field', 'penerima'))->findOrFail($id);
    
//     // 2. Cek status untuk conditional TTD
//     $is_approved = in_array($letter->status, array('disetujui', 'selesai', 'arsip'));
    
//     // ✅ 3. DEFINISIKAN generated_at SEBELUM DIGUNAKAN (Ini yang kurang!)
//     $generated_at = date('d F Y');
    
//     // 4. Generate Signature Kabag
//     $signatureKabag = null;
//     if ($letter->creator && $letter->creator->signature) {
//         $fileName = trim($letter->creator->signature);
//         $path = storage_path('app/public/signatures/' . $fileName);
//         if (file_exists($path)) {
//             $type = pathinfo($path, PATHINFO_EXTENSION);
//             $data = file_get_contents($path);
//             $signatureKabag = 'data:image/' . $type . ';base64,' . base64_encode($data);
//         }
//     }
    
//     // 5. Generate Signature Dirut (hanya jika approved)
//     $signatureDirut = null;
//     if ($is_approved && $letter->approver && $letter->approver->signature) {
//         $fileName = trim($letter->approver->signature);
//         $path = storage_path('app/public/signatures/' . $fileName);
//         if (file_exists($path)) {
//             $type = pathinfo($path, PATHINFO_EXTENSION);
//             $data = file_get_contents($path);
//             $signatureDirut = 'data:image/' . $type . ';base64,' . base64_encode($data);
//         }
//     }
    
//     // ✅ 6. GUNAKAN compact() - PASTIKAN SEMUA VARIABEL SUDAH ADA DI ATAS
//     $pdf = Pdf::loadView('letters.pdf', compact(
//         'letter', 
//         'signatureKabag', 
//         'signatureDirut', 
//         'is_approved', 
//         'generated_at'  // ✅ Sekarang aman karena sudah didefinisikan di langkah 3
//     ))
//     ->setOption(array(
//         'isRemoteEnabled' => true,
//         'isHtml5ParserEnabled' => true,
//         'chroot' => array(storage_path('app/public'), base_path('public')),
//     ))
//     ->setPaper('a4', 'portrait');
    
//     return $pdf->stream('Surat-' . $letter->nomor_surat . '.pdf');
// }
public function downloadPdf($id)
{
    $letter = Letter::with(array('creator', 'approver', 'values.field', 'penerima'))->findOrFail($id);
    
    // Cek status untuk conditional TTD
    $is_approved = in_array($letter->status, array('disetujui', 'selesai', 'arsip'));
    
    // ✅ PASTIKAN VARIABEL INI ADA
    $generated_at = date('d F Y');
    
    // Generate Signature Kabag
    $signatureKabag = null;
    if ($letter->creator && $letter->creator->signature) {
        $fileName = trim($letter->creator->signature);
        $path = storage_path('app/public/signatures/' . $fileName);
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $signatureKabag = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }
    
    // Generate Signature Dirut
    $signatureDirut = null;
    if ($is_approved && $letter->approver && $letter->approver->signature) {
        $fileName = trim($letter->approver->signature);
        $path = storage_path('app/public/signatures/' . $fileName);
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $signatureDirut = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }
    
    // ✅ TAMBAHKAN DD() DI SINI UNTUK DEBUG
    // dd([
    //     'letter_id' => $letter->id,
    //     'nomor_surat' => $letter->nomor_surat,
    //     'status_surat' => $letter->status,
    //     'is_approved' => $is_approved,
    //     'generated_at' => $generated_at,
    //     'approved_by_db' => $letter->approved_by,
    //     'approver_data' => $letter->approver ? [
    //         'id' => $letter->approver->id,
    //         'nama' => $letter->approver->nama_lengkap,
    //         'level' => $letter->approver->level,
    //         'signature_file' => $letter->approver->signature,
    //     ] : 'NULL - Approver tidak ditemukan!',
    //     'signatureKabag' => $signatureKabag ? 'ADA (Base64 ' . strlen($signatureKabag) . ' chars)' : 'NULL',
    //     'signatureDirut' => $signatureDirut ? 'ADA (Base64 ' . strlen($signatureDirut) . ' chars)' : 'NULL',
    //     'creator_data' => $letter->creator ? [
    //         'id' => $letter->creator->id,
    //         'nama' => $letter->creator->nama_lengkap,
    //         'signature_file' => $letter->creator->signature,
    //     ] : 'NULL',
    // ]);
    
    // BARIS INI JANGAN DIJALANKAN DULU (comment out sementara)
    
    $pdf = Pdf::loadView('letters.pdf', compact(
        'letter', 
        'signatureKabag', 
        'signatureDirut', 
        'is_approved', 
        'generated_at'
    ))
    ->setOption(array(
        'isRemoteEnabled' => true,
        'isHtml5ParserEnabled' => true,
        'chroot' => array(storage_path('app/public'), base_path('public')),
    ))
    ->setPaper('a4', 'portrait');
    
    return $pdf->stream('Surat-' . $letter->nomor_surat . '.pdf');
    
}
    public function printPdf($id)
    {
        $letter = Letter::with(array('creator', 'values.field', 'penerima'))->findOrFail($id);
        
        $signatureBase64 = null;
        if ($letter->creator && $letter->creator->signature) {
            $fileName = trim($letter->creator->signature); 
            $path = storage_path('app/public/signatures/' . $fileName);
            $fullPath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
            
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                $signatureBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $pdf = Pdf::loadView('letters.pdf', compact('letter', 'signatureBase64'))
                  ->setOption(array(
                      'isRemoteEnabled' => true,
                      'chroot' => storage_path('app/public'),
                  ));

        $pdf->getDomPDF()->set_option("isPhpEnabled", true);
        $pdf->getDomPDF()->getCanvas()->page_script('
            $pdf->javascript("window.print();");
        ');

        return $pdf->stream('Surat-Print.pdf');
    }
}