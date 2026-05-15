<?php
namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Letter;
use App\Models\LetterValue;
use App\Models\Template;
use App\Models\User;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisposisiController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function inbox()
{
    $user = auth()->user();
    
    // 🔹 BELAJAR: Load relasi agar tidak N+1 query
    $query = Disposisi::with(['letter', 'dari.cabang', 'ke.cabang']);

    // 🔹 BELAJAR: Non-admin hanya lihat disposisi yang ditujukan ke dirinya
    if (!$user->isAdmin()) {
        $query->where('ke_user_id', $user->id);
    }

    // 🔹 BELAJAR: Optional filter via request (jika ingin tambah filter di inbox)
    if (request()->filled('prioritas')) {
        $query->where('prioritas', request('prioritas'));
    }
    
    if (request()->filled('status')) {
        $query->where('status', request('status'));
    }

    // 🔹 BELAJAR: Filter via relasi cabang->tipe (Pusat/Cabang/Unit)
    if (request()->filled('tipe_struktur')) {
        $query->whereHas('dari', function($q) {
            $q->whereHas('cabang', function($c) {
                $c->where('tipe', request('tipe_struktur')); // 'pusat', 'cabang', atau 'unit'
            });
        });
    }

    $disposisi = $query->orderBy('created_at', 'desc')->paginate(10);
    
    return view('disposisi.inbox', compact('disposisi'));
}

    public function store(Request $request)
    {
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
            $filePath = $request->hasFile('file_path') ? $request->file('file_path')->store('letters', 'public') : null;

            $letter = Letter::create([
                'template_id'   => $request->template_id,
                'nomor_surat'   => $request->nomor_surat,
                'tanggal'       => $request->tanggal,
                'perihal'       => $request->perihal,
                'jenis'         => $template->jenis,
                'status'        => 'menunggu_verifikasi',
                'current_level' => 1,
                'created_by'    => auth()->id(),
                'ke_user_id'    => $request->ke_user_id,
                'file_path'     => $filePath,
            ]);

            if ($request->has('fields') && is_array($request->fields)) {
                foreach ($request->fields as $fieldId => $value) {
                    if ($value !== null && $value !== '') {
                        LetterValue::create(['letter_id' => $letter->id, 'field_id' => $fieldId, 'nilai' => trim($value)]);
                    }
                }
            }

            // 🔹 BELAJAR ALUR: Validasi routing SERAHKAN ke Model User. 
            // Model sudah paham Pusat/Cabang/Unit + Level hierarchy.
            $sender = auth()->user();
            $target = User::find($request->ke_user_id);
            if ($target && !$sender->canForwardTo($target)) {
                DB::rollBack();
                return back()->withInput()->with('error', '❌ Routing tidak diizinkan. Pastikan tujuan berada dalam struktur/unit yang sesuai.');
            }

            Disposisi::create([
                'letter_id'    => $letter->id,
                'dari_user_id' => auth()->id(),
                'ke_user_id'   => $request->ke_user_id,
                'instruksi'    => 'Surat baru - mohon ditindaklanjuti',
                'prioritas'    => 'biasa',
                'status'       => 'pending',
                'deadline'     => now()->addDays(3),
            ]);

            DB::commit();
            return redirect()->route('letters.index')->with('success', '✅ Surat berhasil dibuat dan diteruskan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'status'     => 'nullable|in:pending,dibaca,diproses,diteruskan,dikembalikan,selesai',
            'action'     => 'required|in:approve,forward,return,reject',
            'instruksi'  => 'nullable|string|max:500',
            'ke_user_id' => 'nullable|exists:users,id',
            'prioritas'  => 'nullable|in:biasa,penting,segera,rahasia',
        ]);

        $disposisi = Disposisi::with('letter')->findOrFail($id);
        $user      = auth()->user();

        // 🔹 BELAJAR ALUR: Validasi routing dipusatkan di User::canForwardTo()
        // Tidak perlu switch-case manual lagi. Ini mencegah logic drift.
        if ($request->filled('ke_user_id')) {
            $targetUser = User::find($request->ke_user_id);
            if ($targetUser && !$user->canForwardTo($targetUser)) {
                return redirect()->back()->with('error', '❌ Anda tidak dapat meneruskan ke user ini (melanggar aturan struktur/level).');
            }
        }

        $isLeader   = in_array($user->level, ['kabag', 'kacab', 'dirut', 'admin']);
        $isVerifier = in_array($user->level, ['kasubag', 'kasie', 'kanit', 'kabag', 'kacab', 'dirut', 'admin']);

        if ($request->action === 'return' && !in_array($user->level, ['kasubag', 'kasie', 'kanit'])) {
            return redirect()->back()->with('error', '❌ Hanya Kasubag/Kasie/Kanit yang boleh mengembalikan ke Staff.');
        }
        if ($request->action === 'forward' && !$isVerifier) {
            return redirect()->back()->with('error', '❌ Anda tidak memiliki wewenang untuk meneruskan disposisi.');
        }

        switch ($request->action) {
            case 'approve':
            case 'forward':
                $targetStatus = 'diteruskan';
                break;
            case 'return':
                $targetStatus = 'dikembalikan';
                break;
            case 'reject':
                $targetStatus = 'selesai';
                break;
            default:
                $targetStatus = 'diproses';
                break;
        }

        DB::beginTransaction();
        try {
            $updateData = ['status' => $targetStatus, 'updated_at' => now()];
            if ($request->filled('instruksi')) {
                $updateData['instruksi'] = $isLeader 
                    ? $request->instruksi 
                    : '[Verifikator: ' . $user->nama_lengkap . '] ' . $request->instruksi;
            }
            $disposisi->update($updateData);

            if (in_array($request->action, ['forward', 'return']) && $request->filled('ke_user_id')) {
                $nextUser = User::findOrFail($request->ke_user_id);
                Disposisi::create([
                    'letter_id'    => $disposisi->letter_id,
                    'parent_id'    => $disposisi->id,
                    'dari_user_id' => $user->id,
                    'ke_user_id'   => $nextUser->id,
                    'instruksi'    => $request->action === 'return' ? 'Revisi: ' . ($request->instruksi ?: 'Perbaiki sesuai ketentuan') : ($request->instruksi ?: 'Mohon ditindaklanjuti'),
                    'status'       => $request->action === 'return' ? 'draft' : 'pending',
                    'prioritas'    => $request->prioritas ?: 'biasa',
                    'deadline'     => $request->action !== 'return' ? ($request->deadline ?: now()->addDays(3)) : null,
                ]);
            }

            if ($request->action === 'reject') {
                $disposisi->letter->update(['status' => 'selesai']);
            } elseif ($targetStatus === 'dikembalikan') {
                $disposisi->letter->update(['status' => 'diproses']);
            } else {
                $this->syncLetterStatus($disposisi->letter_id);
            }

            if ($request->filled('ke_user_id') && $request->action !== 'reject') {
                app(NotifikasiService::class)->kirim(
                    User::find($request->ke_user_id),
                    $disposisi->letter,
                    $disposisi,
                    $request->action === 'return' ? 'Surat dikembalikan untuk revisi' : 'Tugas/disposisi baru'
                );
            }

            DB::commit();
            return redirect()->back()->with('success', '✅ Proses berhasil');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Process Error: ' . $e->getMessage());
            return redirect()->back()->with('error', ' Gagal: ' . $e->getMessage());
        }
    }

    public function reply(Request $request, $id)
    {
        $request->validate(array(
            'instruksi' => 'required|string|max:500',
            'prioritas' => 'nullable|in:biasa,penting,segera',
        ));

        $disposisi = Disposisi::findOrFail($id);

        DB::beginTransaction();
        try {
            Disposisi::create(array(
                'letter_id'      => $disposisi->letter_id,
                'parent_id'      => $disposisi->id,
                'dari_user_id'   => auth()->id(),
                'ke_user_id'     => $disposisi->dari_user_id,
                'instruksi'      => $request->instruksi,
                'prioritas'      => $request->prioritas ?: 'biasa',
                'status'         => 'menunggu_verifikasi',
                'deadline'       => now()->addDays(3),
                'balasan'        => '1',
            ));

            DB::commit();
            return redirect()->back()->with('success', '✅ Balasan berhasil dikirim');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    private function syncLetterStatus($letterId)
    {
        $letter = Letter::find($letterId);
        if (!$letter) return;

        $allDisposisis = Disposisi::where('letter_id', $letterId)->get();
        
        // Status aktif yang masih perlu diproses (exclude 'selesai' & 'dikembalikan')
        $activeStatuses = array('pending', 'dibaca', 'diproses', 'diteruskan');
        $hasActive = $allDisposisis->whereIn('status', $activeStatuses)->isNotEmpty();

        // Cek apakah ada yang reject final
        $hasRejected = $allDisposisis->where('status', 'selesai')
                            ->whereNotNull('instruksi')
                            ->isNotEmpty();

        if ($hasActive) {
            $newStatus = 'diproses';
        } elseif ($hasRejected) {
            $newStatus = 'ditolak';
        } else {
            $newStatus = 'disetujui';
            
            // ✅ CATAT APPROVAL: siapa yang menyetujui final (UPDATED)
            $finalApprover = Disposisi::where('letter_id', $letterId)
                ->whereIn('status', array('diteruskan', 'selesai'))
                ->whereHas('ke', function($q) {
                    $q->whereIn('level', array('kabag', 'kacab', 'dirut', 'admin'));
                })
                ->latest('updated_at')
                ->first();
                
            if ($finalApprover && !$letter->approved_by) {
                $letter->update(array(
                    'approved_by' => $finalApprover->ke_user_id,
                    'approved_at' => $finalApprover->updated_at,
                ));
            }
        }
        
        if ($letter->status !== $newStatus) {
            $letter->update(array('status' => $newStatus));
            Log::info("✅ Letter #{$letterId} sync to: {$newStatus}");
        }
    }
    
    public function show($id)
    {
        $disposisi = Disposisi::with(['letter.template','letter.values.field','letter.creator','letter.approver','letter.penerima','dari','ke','parent.dari','parent.ke'])->findOrFail($id);
        $user = auth()->user();

        if (!$user->isAdmin() && $disposisi->ke_user_id != $user->id && $disposisi->dari_user_id != $user->id) {
            abort(403, 'Anda tidak memiliki akses ke disposisi ini');
        }

        if ($disposisi->status == 'pending' && $disposisi->ke_user_id == $user->id) {
            $disposisi->update(['status' => 'dibaca']);
        }

        $availableUsers = $user->getAvailableForwardTargets();
        return view('disposisi.show', compact('disposisi', 'availableUsers'));
    }

    public function all(Request $request)
{
    // 🔹 BELAJAR: Hanya admin yang boleh akses halaman ini
    if (!auth()->user()->isAdmin()) {
        abort(403, 'Akses ditolak');
    }

    $query = Disposisi::with(['letter', 'dari.cabang', 'ke.cabang']);

    // 🔹 BELAJAR: Filter via relasi cabang->tipe (Pusat/Cabang/Unit)
    if ($request->filled('tipe_struktur')) {
        $query->whereHas('dari', function($q) use ($request) {
            $q->whereHas('cabang', function($c) use ($request) {
                $c->where('tipe', $request->tipe_struktur);
            });
        });
    }

    // 🔹 BELAJAR: Search nomor surat / perihal via relasi letter
    if ($request->filled('search')) {
        $query->whereHas('letter', function($q) use ($request) {
            $q->where('nomor_surat', 'like', '%'.$request->search.'%')
              ->orWhere('perihal', 'like', '%'.$request->search.'%');
        });
    }

    // 🔹 BELAJAR: Filter level tujuan (Kabag/Kacab) - pastikan scope di Disposisi model sudah update
    if ($request->filled('level')) {
        if ($request->level === 'kabag') {
            $query->toKabag(); // Scope: where level='kabag' AND cabang->tipe='pusat'
        } elseif ($request->level === 'kacab') {
            $query->toKacab(); // Scope: where level='kacab' AND cabang->tipe='cabang'
        }
    }

    // 🔹 BELAJAR: Filter lintas struktur (Pusat ↔ Cabang)
    if ($request->filled('lintas') && $request->lintas === '1') {
        $query->crossStructure();
    }

    // 🔹 BELAJAR: Filter status & prioritas (opsional)
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->filled('prioritas')) {
        $query->where('prioritas', $request->prioritas);
    }

    $disposisis = $query->latest()->paginate(20);
    
    return view('disposisi.all', compact('disposisis'));
}
}