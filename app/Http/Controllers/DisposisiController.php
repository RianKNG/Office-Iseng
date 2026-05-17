<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Disposisi;
use App\Models\Letter;
use App\Models\LetterValue;
use App\Models\Notification;
use App\Models\Template;
use App\Models\User;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisposisiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function inbox()
    {
        $user = auth()->user();
        
        // Load relasi agar tidak N+1 query
        $query = Disposisi::with(array('letter', 'dari.cabang', 'ke.cabang'));

        // Non-admin hanya lihat disposisi yang ditujukan ke dirinya
        if (!$user->isAdmin()) {
            $query->where('ke_user_id', $user->id);
        }

        // Optional filter via request
        if (request()->filled('prioritas')) {
            $query->where('prioritas', request('prioritas'));
        }
        
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        // Filter via relasi cabang->tipe
        if (request()->filled('tipe_struktur')) {
            $query->whereHas('dari', function($q) {
                $q->whereHas('cabang', function($c) {
                    $c->where('tipe', request('tipe_struktur'));
                });
            });
        }

        $disposisi = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('disposisi.inbox', compact('disposisi'));
    }

    public function store(Request $request)
    {
        $request->validate(array(
            'template_id' => 'required|exists:templates,id',
            'nomor_surat' => 'required|string|max:100',
            'tanggal'     => 'required|date',
            'perihal'     => 'required|string|max:255',
            'ke_user_id'  => 'required|exists:users,id',
            'fields.*'    => 'nullable|string|max:1000',
            'file_path'   => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
        ));

        DB::beginTransaction();
        try {
            $template = Template::findOrFail($request->template_id);
            $filePath = $request->hasFile('file_path') ? $request->file('file_path')->store('letters', 'public') : null;

            $letter = Letter::create(array(
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
            ));

            if ($request->has('fields') && is_array($request->fields)) {
                foreach ($request->fields as $fieldId => $value) {
                    if ($value !== null && $value !== '') {
                        LetterValue::create(array(
                            'letter_id' => $letter->id,
                            'field_id' => $fieldId,
                            'nilai' => trim($value)
                        ));
                    }
                }
            }

            // Validasi routing via Model User
            $sender = auth()->user();
            $target = User::find($request->ke_user_id);
            if ($target && !$sender->canForwardTo($target)) {
                DB::rollBack();
                return back()->withInput()->with('error', '❌ Routing tidak diizinkan.');
            }

            Disposisi::create(array(
                'letter_id'    => $letter->id,
                'dari_user_id' => auth()->id(),
                'ke_user_id'   => $request->ke_user_id,
                'instruksi'    => 'Surat baru - mohon ditindaklanjuti',
                'prioritas'    => 'biasa',
                'status'       => 'pending',
                'deadline'     => now()->addDays(3),
            ));

            DB::commit();
            return redirect()->route('letters.index')->with('success', '✅ Surat berhasil dibuat dan diteruskan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    // public function process(Request $request, $id)
    // {
    //     // ✅ FIX: Tambah 'proses' dan 'selesai' ke validasi action
    //     $request->validate(array(
    //         'status'     => 'nullable|in:pending,dibaca,diproses,diteruskan,dikembalikan,selesai',
    //         'action'     => 'required|in:approve,forward,return,reject,proses,selesai',
    //         'instruksi'  => 'nullable|string|max:500',
    //         'ke_user_id' => 'nullable|exists:users,id',
    //         'prioritas'  => 'nullable|in:biasa,penting,segera,rahasia',
    //     ));

    //     $disposisi = Disposisi::with('letter')->findOrFail($id);
    //     $user      = auth()->user();

    //     // Validasi routing
    //     if ($request->filled('ke_user_id')) {
    //         $targetUser = User::find($request->ke_user_id);
    //         if ($targetUser && !$user->canForwardTo($targetUser)) {
    //             return redirect()->back()->with('error', '❌ Anda tidak dapat meneruskan ke user ini.');
    //         }
    //     }

    //     $isLeader   = in_array($user->level, array('kabag', 'kacab', 'dirut', 'admin'));
    //     $isVerifier = in_array($user->level, array('kasubag', 'kasie', 'kanit', 'kabag', 'kacab', 'dirut', 'admin'));

    //     if ($request->action === 'return' && !in_array($user->level, array('kasubag', 'kasie', 'kanit'))) {
    //         return redirect()->back()->with('error', '❌ Hanya Kasubag/Kasie/Kanit yang boleh mengembalikan ke Staff.');
    //     }
    //     if ($request->action === 'forward' && !$isVerifier) {
    //         return redirect()->back()->with('error', '❌ Anda tidak memiliki wewenang untuk meneruskan disposisi.');
    //     }

    //     // ✅ FIX: Switch case sudah include 'proses' dan 'selesai'
    //     switch ($request->action) {
    //         case 'approve':
    //         case 'forward':
    //             $targetStatus = 'diteruskan';
    //             break;
    //         case 'return':
    //             $targetStatus = 'dikembalikan';
    //             break;
    //         case 'reject':
    //             $targetStatus = 'ditolak';
    //             break;
    //         case 'proses':
    //             $targetStatus = 'diproses';
    //             break;
    //         case 'selesai':
    //             $targetStatus = 'selesai';
    //             break;
    //         default:
    //             $targetStatus = 'diproses';
    //             break;
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $updateData = array('status' => $targetStatus, 'updated_at' => now());
    //         if ($request->filled('instruksi')) {
    //             $updateData['instruksi'] = $isLeader 
    //                 ? $request->instruksi 
    //                 : '[Verifikator: ' . $user->nama_lengkap . '] ' . $request->instruksi;
    //         }
    //         $disposisi->update($updateData);

    //         if (in_array($request->action, array('forward', 'return')) && $request->filled('ke_user_id')) {
    //             $nextUser = User::findOrFail($request->ke_user_id);
    //             Disposisi::create(array(
    //                 'letter_id'    => $disposisi->letter_id,
    //                 'parent_id'    => $disposisi->id,
    //                 'dari_user_id' => $user->id,
    //                 'ke_user_id'   => $nextUser->id,
    //                 'instruksi'    => $request->action === 'return' ? 'Revisi: ' . ($request->instruksi ?: 'Perbaiki sesuai ketentuan') : ($request->instruksi ?: 'Mohon ditindaklanjuti'),
    //                 'status'       => $request->action === 'return' ? 'draft' : 'pending',
    //                 'prioritas'    => $request->prioritas ?: 'biasa',
    //                 'deadline'     => $request->action !== 'return' ? ($request->deadline ?: now()->addDays(3)) : null,
    //             ));
    //         }

    //         if ($request->action === 'reject') {
    //             $disposisi->letter->update(array('status' => 'ditolak'));
    //         } elseif ($targetStatus === 'dikembalikan') {
    //             $disposisi->letter->update(array('status' => 'diproses'));
    //         } else {
    //             // ✅ PASTIKAN INI TERPANGGIL untuk auto-update status surat
    //             $this->syncLetterStatus($disposisi->letter_id);
    //         }

    //         if ($request->filled('ke_user_id') && $request->action !== 'reject') {
    //             app(NotifikasiService::class)->kirim(
    //                 User::find($request->ke_user_id),
    //                 $disposisi->letter,
    //                 $disposisi,
    //                 $request->action === 'return' ? 'Surat dikembalikan untuk revisi' : 'Tugas/disposisi baru'
    //             );
    //         }

    //         DB::commit();
    //         return redirect()->back()->with('success', '✅ Proses berhasil');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Process Error: ' . $e->getMessage());
    //         return redirect()->back()->with('error', ' Gagal: ' . $e->getMessage());
    //     }
    // }
//     public function process(Request $request, $id)
// {
//     // 🔍 DEBUG - DUMP SEMUA DATA
//         // Debug sebelum simpan
//     dd([
//         'action' => $request->action,
//         'ke_user_id' => $request->ke_user_id,
//         'dari_user_id' => Auth::id(),
//         'letter_id' => $request->letter_id,
//     ]);
    
//     DB::transaction(function () use ($request) {
//         $disposisi = Disposisi::create([
//             'letter_id' => $request->letter_id,
//             'dari_user_id' => Auth::id(),
//             'ke_user_id' => $request->ke_user_id,
//             'instruksi' => $request->instruksi,
//             'status' => 'pending',
//         ]);
        
//         // Update approved_by jika Direktur
//         if (Auth::user()->level === 'dirut') {
//             $disposisi->letter->update(['approved_by' => Auth::id()]);
//         }
//     });
//     // d([
//     //     'request_all' => $request->all(),
//     //     'request_action' => $request->action,
//     //     'request_ke_user_id' => $request->ke_user_id,
//     //     'auth_user_id' => Auth::id(),
//     //     'auth_user_level' => Auth::user()->level,
//     //     'auth_user_nama' => Auth::user()->nama_lengkap,
//     //     'letter_id' => $id,
//     // ]);

//     $request->validate(array(
//         'status'     => 'nullable|in:pending,dibaca,diproses,diteruskan,dikembalikan,selesai',
//         'action'     => 'required|in:approve,forward,return,reject,proses,selesai',
//         'instruksi'  => 'nullable|string|max:500',
//         'ke_user_id' => 'nullable|exists:users,id',
//         'prioritas'  => 'nullable|in:biasa,penting,segera,rahasia',
//     ));

//     $disposisi = Disposisi::with('letter')->findOrFail($id);
//     $user      = auth()->user();

//     // ... [validasi routing tetap sama] ...

//     switch ($request->action) {
//         case 'approve':
//         case 'forward':
//             $targetStatus = 'diteruskan';
//             break;
//         case 'return':
//             $targetStatus = 'dikembalikan';
//             break;
//         case 'reject':
//             $targetStatus = 'ditolak';
//             break;
//         case 'proses':
//             $targetStatus = 'diproses';
//             break;
//         case 'selesai':
//             $targetStatus = 'selesai';
//             break;
//         default:
//             $targetStatus = 'diproses';
//             break;
//     }

//     DB::beginTransaction();
//     try {
//         $updateData = array('status' => $targetStatus, 'updated_at' => now());
//         if ($request->filled('instruksi')) {
//             $updateData['instruksi'] = $isLeader 
//                 ? $request->instruksi 
//                 : '[Verifikator: ' . $user->nama_lengkap . '] ' . $request->instruksi;
//         }
//         $disposisi->update($updateData);

//         // ✅ FIX: Set approved_by jika Direktur yang approve
//        // Di dalam method process(), tepat sebelum kode if Anda:
// \Log::info('DEBUG ACTION: ' . $request->action . ' | USER LEVEL: ' . $user->level);

// if ($user->isDirut() && in_array($request->action, ['approve', 'selesai','forward'])) {
//      dd([
//         'condition_met' => true,
//         'user_level' => $user->level,
//         'action' => $request->action,
//         'current_approved_by' => $disposisi->letter->approved_by,
//         'will_update_to' => $user->id,
//     ]);

//     \Log::info('✅ LOGIC DIRUT APPROVE TERPICU');
//     if (is_null($disposisi->letter->approved_by)) {
//         $disposisi->letter->update(['approved_by' => $user->id]);
//         \Log::info('✅ APPROVED_BY BERHASIL DI-UPDATE KE: ' . $user->id);
//     }
// }

//         if (in_array($request->action, array('forward', 'return')) && $request->filled('ke_user_id')) {
//             $nextUser = User::findOrFail($request->ke_user_id);
//             Disposisi::create(array(
//                 'letter_id'    => $disposisi->letter_id,
//                 'parent_id'    => $disposisi->id,
//                 'dari_user_id' => $user->id,
//                 'ke_user_id'   => $nextUser->id,
//                 'instruksi'    => $request->action === 'return' ? 'Revisi: ' . ($request->instruksi ?: 'Perbaiki sesuai ketentuan') : ($request->instruksi ?: 'Mohon ditindaklanjuti'),
//                 'status'       => $request->action === 'return' ? 'draft' : 'pending',
//                 'prioritas'    => $request->prioritas ?: 'biasa',
//                 'deadline'     => $request->action !== 'return' ? ($request->deadline ?: now()->addDays(3)) : null,
//             ));
//         }

//         if ($request->action === 'reject') {
//             $disposisi->letter->update(array('status' => 'ditolak'));
//         } elseif ($targetStatus === 'dikembalikan') {
//             $disposisi->letter->update(array('status' => 'diproses'));
//         } else {
//             $this->syncLetterStatus($disposisi->letter_id);
//         }

//         if ($request->filled('ke_user_id') && $request->action !== 'reject') {
//             app(NotifikasiService::class)->kirim(
//                 User::find($request->ke_user_id),
//                 $disposisi->letter,
//                 $disposisi,
//                 $request->action === 'return' ? 'Surat dikembalikan untuk revisi' : 'Tugas/disposisi baru'
//             );
//         }

//         DB::commit();
//         return redirect()->back()->with('success', '✅ Proses berhasil');
//     } catch (\Exception $e) {
//         DB::rollBack();
//         Log::error('Process Error: ' . $e->getMessage());
//         return redirect()->back()->with('error', ' Gagal: ' . $e->getMessage());
//     }
// }
public function process(Request $request, $id)
{
     $disposisi = Disposisi::findOrFail($id);
    $letter = $disposisi->letter;
    $user = Auth::user(); // ✅ Definisi $user di luar closure
    
    DB::transaction(function () use ($request, $disposisi, $letter, $user) { // ✅ Tambahkan $user di use()
        
        // 1. Handle forward
        if ($request->action === 'forward' && $request->filled('ke_user_id')) {
            Disposisi::create([
                'letter_id' => $letter->id,
                'dari_user_id' => $user->id,
                'ke_user_id' => $request->ke_user_id,
                'instruksi' => $request->instruksi,
                'status' => 'pending',
                'parent_id' => $disposisi->id,
            ]);
        }
        
        // 2. Update approved_by jika Direktur
        if ($user->level === 'dirut' && in_array($request->action, ['approve', 'forward', 'selesai'])) {
            if (is_null($letter->approved_by)) {
                $letter->approved_by = $user->id;
                $letter->save();
            }
        }
        
        // 3. Update status
        // 1. Buat pemetaan status menggunakan array (Aman untuk PHP 7)
$statusMapping = [
    'approve' => 'disetujui',
    'forward' => 'diproses',
    'selesai' => 'selesai',
    'reject'  => 'ditolak',
];

// 2. Ambil status baru, jika action tidak terdaftar maka gunakan status lama (default)
$newStatus = $statusMapping[$request->action] ?? $letter->status;

// 3. Simpan perubahan ke database
$letter->status = $newStatus;
$letter->save();
        
        // 4. Kirim notifikasi
        if ($request->filled('ke_user_id') && $request->action === 'forward') {
            \App\Models\Notifikasi::create([
                'user_id' => $request->ke_user_id,
                'message' => "📬 Disposisi Baru: {$letter->nomor_surat}\nDari: {$user->nama_lengkap}",
                'disposisi_id' => $disposisi->id,
                'is_read' => false,
                'created_by' => $user->id,
            ]);
        }
    });
    
    return redirect()->back()->with('success', 'Disposisi berhasil diproses.');
}

// {
//     \Log::info('=== DEBUG DISPOSISI PROCESS ===', [
//     'disposisi_id' => $id,
//     'letter_id_from_form' => $request->letter_id,
//     'letter_id_from_relation' => $disposisi->letter->id ?? 'NULL',
//     'action' => $request->action,
//     'ke_user_id' => $request->ke_user_id,
//     'auth_user' => ['id' => $user->id, 'level' => $user->level],
// ]);
//     $disposisi = Disposisi::findOrFail($id);
//     $letter = $disposisi->letter; // Ambil surat dari relasi
//     $user = Auth::user();
    
//     DB::transaction(function () use ($request, $disposisi, $letter, $user) {
        
//         // 1. Update disposisi (jika forward, buat disposisi baru)
//         if ($request->action === 'forward' && $request->filled('ke_user_id')) {
//             Disposisi::create([
//                 'letter_id' => $letter->id,
//                 'dari_user_id' => $user->id,
//                 'ke_user_id' => $request->ke_user_id,
//                 'instruksi' => $request->instruksi,
//                 'status' => 'pending',
//                 'parent_id' => $disposisi->id,
//             ]);
//         }
        
//         // 2. ✅ UPDATE approved_by JIKA DIREKTUR
//         if ($user->level === 'dirut' && in_array($request->action, ['approve', 'forward', 'selesai'])) {
//             if (is_null($letter->approved_by)) {
//                 $letter->approved_by = $user->id;
//                 $letter->save();
//                 \Log::info("✅ approved_by di-set ke Direktur ID: {$user->id}");
//             }
//         }
        
//         // 3. Update status surat
//         switch ($request->action) {
//     case 'approve':
//         $newStatus = 'disetujui';
//         break;
//     case 'forward':
//         $newStatus = 'diproses';
//         break;
//     case 'selesai':
//         $newStatus = 'selesai';
//         break;
//     case 'reject':
//         $newStatus = 'ditolak';
//         break;
//     default:
//         $newStatus = $letter->status;
//         break;
// }
//         $letter->status = $newStatus;
//         $letter->save();
        
//         // 4. ✅ KIRIM NOTIFIKASI KE PENERIMA
//         if ($request->filled('ke_user_id') && $request->action === 'forward') {
//             Notification::create([
//                 'user_id' => $request->ke_user_id,
//                 'message' => "📬 Disposisi Baru: {$letter->nomor_surat} - {$letter->perihal}\nDari: {$user->nama_lengkap}\nInstruksi: {$request->instruksi}",
//                 'disposisi_id' => $disposisi->id,
//                 'is_read' => false,
//                 'created_by' => $user->id,
//             ]);
//             \Log::info("✅ Notifikasi dikirim ke User ID: {$request->ke_user_id}");
//         }
//     });
    
//     return redirect()->back()->with('success', 'Disposisi berhasil diproses.');
// }
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

    /**
     * Sync status surat berdasarkan status semua disposisinya
     */
    private function syncLetterStatus($letterId)
{
    $letter = Letter::find($letterId);
    if (!$letter) return;

    $allDisposisis = Disposisi::where('letter_id', $letterId)->get();
    
    // ✅ FIX: HANYA 3 status ini yang dianggap "sedang berjalan"
    $activeStatuses = array('pending', 'dibaca', 'diproses');
    
    // Cek apakah masih ada disposisi yang benar-benar aktif
    $hasActive = false;
    foreach ($allDisposisis as $d) {
        if (in_array($d->status, $activeStatuses)) {
            $hasActive = true;
            break;
        }
    }

    // ✅ LOGIKA: 
    // - Jika masih ada pending/dibaca/diproses → surat DIPROSES
    // - Jika semua sudah diteruskan/selesai/dikembalikan → surat SELESAI
    if ($hasActive) {
        $newStatus = 'diproses';
    } else {
        $newStatus = 'selesai';
    }
    
    // Update hanya jika status berubah
    if ($letter->status !== $newStatus) {
        $letter->update(array('status' => $newStatus));
        Log::info("✅ Letter #{$letterId} sync to: {$newStatus}");
    }
}
    public function show($id)
    {
        $disposisi = Disposisi::with(array(
            'letter.template',
            'letter.values.field',
            'letter.creator',
            'letter.approver',
            'letter.penerima',
            'dari',
            'ke',
            'parent.dari',
            'parent.ke'
        ))->findOrFail($id);
        
        $user = auth()->user();

        if (!$user->isAdmin() && $disposisi->ke_user_id != $user->id && $disposisi->dari_user_id != $user->id) {
            abort(403, 'Anda tidak memiliki akses ke disposisi ini');
        }

        // Auto-update status ke 'dibaca' jika masih pending
        if ($disposisi->status == 'pending' && $disposisi->ke_user_id == $user->id) {
            $disposisi->update(array('status' => 'dibaca'));
        }

        $availableUsers = $user->getAvailableForwardTargets();
        return view('disposisi.show', compact('disposisi', 'availableUsers'));
    }

    public function all(Request $request)
    {
        // Hanya admin yang boleh akses
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak');
        }

        $query = Disposisi::with(array('letter', 'dari.cabang', 'ke.cabang'));

        // Filter via relasi cabang->tipe
        if ($request->filled('tipe_struktur')) {
            $query->whereHas('dari', function($q) use ($request) {
                $q->whereHas('cabang', function($c) use ($request) {
                    $c->where('tipe', $request->tipe_struktur);
                });
            });
        }

        // Search nomor surat / perihal
        if ($request->filled('search')) {
            $query->whereHas('letter', function($q) use ($request) {
                $q->where('nomor_surat', 'like', '%'.$request->search.'%')
                  ->orWhere('perihal', 'like', '%'.$request->search.'%');
            });
        }

        // Filter level tujuan
        if ($request->filled('level')) {
            if ($request->level === 'kabag') {
                $query->toKabag();
            } elseif ($request->level === 'kacab') {
                $query->toKacab();
            }
        }

        // Filter lintas struktur
        if ($request->filled('lintas') && $request->lintas === '1') {
            $query->crossStructure();
        }

        // Filter status & prioritas
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