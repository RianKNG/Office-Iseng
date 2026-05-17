<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\Disposisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Tampilan halaman laporan
     */
    public function index(Request $request)
    {
        // Filter defaults
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->input('status', 'all');
        $template = $request->input('template', 'all');
        $search = $request->input('search', '');
        
        // Query surat dengan filter
        $query = Letter::with(['creator', 'approver', 'template', 'penerima'])
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        // Filter status
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Filter template
        if ($template !== 'all') {
            $query->where('template_id', $template);
        }
        
        // Pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_surat', 'LIKE', "%{$search}%")
                  ->orWhere('perihal', 'LIKE', "%{$search}%")
                  ->orWhereHas('creator', function($sub) use ($search) {
                      $sub->where('nama_lengkap', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // $letters = $query->orderBy('created_at', 'desc')->paginate(20);
        $letters = $query->paginate(20)->appends(request()->query());
        
        // Data untuk dropdown filter
        $templates = \App\Models\Template::all();
        $statuses = ['menunggu_verifikasi', 'diproses', 'disetujui', 'selesai', 'ditolak'];
        
        // Statistik
        $stats = [
            'total' => Letter::whereBetween('created_at', [$startDate, $endDate])->count(),
            'menunggu_verifikasi' => Letter::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'menunggu_verifikasi')->count(),
            'diproses' => Letter::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'diproses')->count(),
            'disetujui' => Letter::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'disetujui')->count(),
            'selesai' => Letter::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'selesai')->count(),
        ];
        
        return view('reports.index', compact(
            'letters', 'templates', 'statuses', 'stats',
            'startDate', 'endDate', 'status', 'template', 'search'
        ));
    }
    
    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->input('status', 'all');
        $template = $request->input('template', 'all');
        $search = $request->input('search', '');
        
        // Query sama seperti index
        $query = Letter::with(['creator', 'approver', 'template', 'penerima'])
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        if ($template !== 'all') {
            $query->where('template_id', $template);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_surat', 'LIKE', "%{$search}%")
                  ->orWhere('perihal', 'LIKE', "%{$search}%");
            });
        }
        
        $letters = $query->orderBy('created_at', 'desc')->get();
        
        // Statistik
        $stats = [
            'total' => $letters->count(),
            'menunggu_verifikasi' => $letters->where('status', 'menunggu_verifikasi')->count(),
            'diproses' => $letters->where('status', 'diproses')->count(),
            'disetujui' => $letters->where('status', 'disetujui')->count(),
            'selesai' => $letters->where('status', 'selesai')->count(),
        ];
        
        $pdf = Pdf::loadView('reports.pdf', compact(
            'letters', 'stats', 'startDate', 'endDate', 'status', 'template', 'search'
        ))
        ->setPaper('a4', 'landscape')
        ->setOption([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);
        
        $filename = 'Laporan_Surat_' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
    
    /**
     * Export Excel (Opsional)
     */
    public function exportExcel(Request $request)
    {
        // Implementasi jika perlu export Excel
        return redirect()->back()->with('info', 'Fitur export Excel akan segera hadir');
    }
}