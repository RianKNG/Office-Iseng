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
    
    // ✅ PENTING: Gunakan helper routing untuk filter user yang VALID
    $users = auth()->user()->getAvailableForwardTargets();

    return view('letters.create', compact('templates', 'users'));
}

    public function store(Request $request)
    {
        Log::info('=== STORE LETTER ===', [
            'user_id' => auth()->id(),
            'request' => $request->all()
        ]);

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

            // ✅ VALIDASI ROUTING: Cek apakah user boleh forward ke target
            $sender = auth()->user();
            $target = User::find($request->ke_user_id);
            
            if ($target && !$sender->canForwardTo($target)) {
                DB::rollBack();
                return back()->withInput()->with('error', 
                    '❌ Anda tidak dapat meneruskan surat ke user ini (beda struktur/unit).');
            }

            // 3. Buat Disposisi Awal
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
            
            Log::info('Letter created successfully', ['letter_id' => $letter->id]);
            
            return redirect()->route('letters.index')
                ->with('success', '✅ Surat berhasil dibuat dan diteruskan.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Store Letter Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->except(['password'])
            ]);
            
            return back()
                ->withInput()
                ->with('error', '❌ Gagal: ' . $e->getMessage());
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
        $query = Letter::with(['template', 'creator']);
////---------------------------------------------------------------------------logika tampilkan tanpa filter----------------------------
        // if ($user->level == 'staff') {
        //     $query->where('created_by', $user->id);
        // }
        // Satukan saja staff dan kasubag di filter yang sama
            if ($user->level == 'staff' || $user->level == 'kasubag_kasie') {
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
//--------------------------------------------tidak sama karena tidak mengunci staff
            // 'total'     => Letter::when($user->level == 'staff', fn($q) => $q->where('created_by', $user->id))->count(),
//kita kunxi kasienya-------------------------------------------------------------------------------------
            'total' => Letter::when(in_array($user->level, ['staff', 'kasubag_kasie']), function($q) use ($user) {
                return $q->where('created_by', $user->id);
            })->count(),
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
            $cleanPath = ltrim(trim($letter->creator->signature), '/');
            $path = storage_path('app/public/' . $cleanPath);
            $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                $base64 = base64_encode($data);
                $cleanBase64 = str_replace(["\r", "\n"], '', $base64);
                $signatureBase64 = 'data:image/' . $type . ';base64,' . $cleanBase64;
            }
        }

        return view('letters.show', compact('letter', 'signatureBase64'));
    }

    public function generateNomorSurat(Request $request)
    {
        $kode = $request->input('kode');
        if (!$kode) {
            return response()->json(['error' => 'Kode template tidak ditemukan'], 400);
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
        
        $bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$bulan - 1];
        $nomorSurat = sprintf('%03d', $nomorUrut) . '/' . $kode . '/' . $bulanRomawi . '/' . $tahun;
        
        return response()->json([
            'success' => true,
            'nomor' => $nomorSurat
        ]);
    }

    public function masuk(Request $request)
    {
        $user = auth()->user();
        
        // ✅ HARD FILTER: jenis = 'masuk' (tidak bisa di-override)
        $query = Letter::with(['template', 'creator'])
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

    public function downloadPdf($id)
    {
        $letter = Letter::with(['creator', 'values.field'])->findOrFail($id);
        
        $signatureBase64 = null;
        
        if ($letter->creator && $letter->creator->signature) {
            $fileName = trim($letter->creator->signature); 
            $path = storage_path('app/public/signatures/' . $fileName);
            $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                $signatureBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

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
        
        $signatureBase64 = null;
        if ($letter->creator && $letter->creator->signature) {
            $fileName = trim($letter->creator->signature); 
            $path = storage_path('app/public/signatures/' . $fileName);
            $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            
            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                $signatureBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $pdf = Pdf::loadView('letters.pdf', compact('letter', 'signatureBase64'))
                  ->setOption([
                      'isRemoteEnabled' => true,
                      'chroot' => storage_path('app/public'),
                  ]);

        $pdf->getDomPDF()->set_option("isPhpEnabled", true);
        $pdf->getDomPDF()->getCanvas()->page_script('
            $pdf->javascript("window.print();");
        ');

        return $pdf->stream('Surat-Print.pdf');
    }
}