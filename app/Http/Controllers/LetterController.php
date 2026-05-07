<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterValue;
use App\Models\Template;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
// Gunakan penulisan ini (perhatikan huruf besar/kecil)


class LetterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
    }

    // public function create()
    // {
    //     $templates = Template::where('is_active', true)->get();
    //     return view('letters.create', compact('templates'));
    // }

    public function create()
{
    $templates = Template::where('is_active', true)
        ->orderBy('jenis')
        ->orderBy('nama_template')
        ->get();
    
    // Ambil user aktif untuk dropdown "kepada"
    $users = \App\Models\User::where('status', 'aktif')
        ->select('id', 'nama_lengkap', 'jabatan', 'level')
        ->orderBy('nama_lengkap')
        ->get();

    return view('letters.create', compact('templates', 'users'));
}

    // API Endpoint untuk AJAX
    public function getFields($templateId)
    {
        $template = Template::with('fields')->findOrFail($templateId);
        return response()->json($template->fields);
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:templates,id',
            'nomor_surat' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'perihal' => 'required|string',
            'fields.*' => 'nullable|string' // Validasi dinamis bisa diperketat di sini
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan Header Surat
            $letter = Letter::create([
                'template_id' => $request->template_id,
                'nomor_surat' => $request->nomor_surat,
                'tanggal' => $request->tanggal,
                'perihal' => $request->perihal,
                'jenis' => Template::find($request->template_id)->jenis,
                'status' => 'draft', // Awal draft
                'current_level' => 1, // Level 1 = Staff
                'created_by' => auth()->id(),
            ]);

            // 2. Simpan Nilai Field Dinamis
            if ($request->has('fields')) {
                foreach ($request->fields as $fieldId => $value) {
                    if (!empty($value)) {
                        LetterValue::create([
                            'letter_id' => $letter->id,
                            'field_id' => $fieldId,
                            'nilai' => $value
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('letters.index')->with('success', 'Surat berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan surat: ' . $e->getMessage());
        }
    }

    // public function index()
    // {
    //     $letters = Letter::with(['template', 'creator'])->latest()->paginate(10);
    //     return view('letters.index', compact('letters'));
    // }
public function index(Request $request)
{
    $user = auth()->user();
    $query = Letter::with(['template', 'creator']);

    if ($user->level == 'staff') {
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

    $letters = $query->latest()->paginate(10);

    // Hitung statistik global (tidak terpengaruh filter halaman)
    $stats = [
        'total'     => Letter::when($user->level == 'staff', fn($q) => $q->where('created_by', $user->id))->count(),
        'waiting'   => Letter::where('status', 'menunggu_verifikasi')->when($user->level == 'staff', fn($q) => $q->where('created_by', $user->id))->count(),
        'approved'  => Letter::where('status', 'disetujui')->when($user->level == 'staff', fn($q) => $q->where('created_by', $user->id))->count(),
        'rejected'  => Letter::where('status', 'ditolak')->when($user->level == 'staff', fn($q) => $q->where('created_by', $user->id))->count(),
    ];

    // 🔹 AJAX Response
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'tableHtml' => view('letters._table_rows', compact('letters'))->render(),
            'pagination' => $letters->withQueryString()->links()->toHtml(),
            'count' => $letters->count(),
            'stats' => $stats
        ]);
    }

    return view('letters.index', compact('letters', 'stats'));
}
    
   public function show($id)
{
    $letter = Letter::with(['template.fields', 'values.field', 'disposisis.dari', 'disposisis.ke', 'creator'])->findOrFail($id);

    $signatureBase64 = null;
    if ($letter->creator && $letter->creator->signature) {
        // Gunakan trim untuk menghindari spasi tak terlihat
        $cleanPath = ltrim(trim($letter->creator->signature), '/');
        
        // Pastikan path menunjuk ke folder yang benar (signatures)
        // Jika di DB hanya nama file, gunakan: 'app/public/signatures/'
        $path = storage_path('app/public/' . $cleanPath);
        $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        
        if (file_exists($fullPath)) {
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);
            $data = file_get_contents($fullPath);
            
            // PERBAIKAN: Harus diawali dengan 'data:image/...'
            $base64 = base64_encode($data);
            $cleanBase64 = str_replace(["\r", "\n"], '', $base64);
            $signatureBase64 = 'data:image/' . $type . ';base64,' . $cleanBase64;
        }
    }

    return view('letters.show', compact('letter', 'signatureBase64'));

}
    public function generateNomorSurat(Request $request)
{
    $kode = $request->input('kode'); // SM-UMUM, SK-RESMI, ND-INT
    $bulan = date('n');
    $tahun = date('Y');
    
    // Hitung nomor urut terakhir
    $lastLetter = Letter::whereYear('created_at', $tahun)
        ->whereMonth('created_at', $bulan)
        ->where('template_id', function($query) use ($kode) {
            $query->select('id')->from('templates')->where('kode_template', $kode);
        })
        ->orderBy('nomor_surat', 'desc')
        ->first();
    
    $nomorUrut = 1;
    if ($lastLetter) {
        // Extract nomor dari "001/SM-UMUM/V/2026"
        preg_match('/^(\d+)\//', $lastLetter->nomor_surat, $matches);
        $nomorUrut = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
    }
    
    $bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$bulan - 1];
    $nomorSurat = sprintf('%03d', $nomorUrut) . '/' . $kode . '/' . $bulanRomawi . '/' . $tahun;
    
    return response()->json(['nomor' => $nomorSurat]);

}
// Di LetterController.php - TAMBAHKAN method ini
public function masuk(Request $request)
{
    $user = auth()->user();
    
    // ✅ HARD FILTER: jenis = 'masuk' (tidak bisa di-override)
    $query = Letter::with(['template', 'creator'])
        ->where('jenis', 'masuk');
        // ->when($user->level == 'staff', fn($q) => $q->where('created_by', $user->id));

    // Search - TETAP pertahankan filter jenis
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('nomor_surat', 'like', '%'.$request->search.'%')
              ->orWhere('perihal', 'like', '%'.$request->search.'%');
        });
    }

    // ❌ JANGAN gunakan filter jenis dari request!
    // if ($request->filled('jenis')) { ... } // <-- KOMENTARI/HAPUS

    // Filter lain tetap boleh
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('from_date')) {
        $query->whereDate('tanggal', '>=', $request->from_date);
    }

    $letters = $query->latest()->paginate(10);

    // Statistik - filter jenis juga di stats
    $statsQuery = Letter::where('jenis', 'masuk')
        ->when($user->level == 'staff', fn($q) => $q->where('created_by', $user->id));
    
    $statsData = (clone $statsQuery)
        ->selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN status = "menunggu_verifikasi" THEN 1 END) as waiting,
            COUNT(CASE WHEN status = "disetujui" THEN 1 END) as approved,
            COUNT(CASE WHEN status = "ditolak" THEN 1 END) as rejected
        ')->first();
    
    $stats = [
        'total'    => $statsData->total,
        'waiting'  => $statsData->waiting,
        'approved' => $statsData->approved,
        'rejected' => $statsData->rejected,
    ];

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'tableHtml' => view('letters._table_rows', compact('letters'))->render(),
            'pagination' => $letters->withQueryString()->links()->toHtml(),
            'count' => $letters->count(),
            'stats' => $stats
        ]);
    }

    return view('letters.index', compact('letters', 'stats'));
}
// Hapus semua fungsi downloadPdf yang lama, ganti dengan SATU blok ini saja
public function downloadPdf($id)
    {
        // 1. Ambil data surat beserta pengirimnya
        $letter = Letter::with(['creator', 'values.field'])->findOrFail($id);
        
        $signatureBase64 = null;
        
        // 2. Proses Pengambilan Gambar Tanda Tangan
        if ($letter->creator && $letter->creator->signature) {
            // Ambil nama file (misal: ttd_admin.png)
            $fileName = trim($letter->creator->signature); 

            // Susun path absolut ke folder storage
            $path = storage_path('app/public/signatures/' . $fileName);

            // Normalisasi path khusus Windows (mengubah / menjadi \)
            $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

            // Cek apakah file fisik benar-benar ada di folder tersebut
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                // Konversi ke Base64 agar DomPDF mudah merender gambar
                $signatureBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        // 3. Render View ke PDF
        $pdf = Pdf::loadView('letters.pdf', compact('letter', 'signatureBase64'))
                  ->setOption([
                      'isRemoteEnabled' => true,
                      'isHtml5ParserEnabled' => true,
                      'chroot' => storage_path('app/public'),
                  ])
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('Surat-' . $letter->nomor_surat . '.pdf');
    }
    public function printPdf($id)
{
    $letter = Letter::with(['creator', 'values.field'])->findOrFail($id);
    
    // ... (Gunakan logika signatureBase64 yang sama seperti downloadPdf) ...

    $pdf = Pdf::loadView('letters.pdf', compact('letter', 'signatureBase64'))
              ->setOption([
                  'isRemoteEnabled' => true,
                  'chroot' => storage_path('app/public'),
              ]);

    // Tambahkan script print otomatis ke dalam PDF
    $pdf->getDomPDF()->set_option("isPhpEnabled", true);
    $pdf->getDomPDF()->getCanvas()->page_script('
        $pdf->javascript("window.print();");
    ');

    return $pdf->stream('Surat-Print.pdf');
}
}