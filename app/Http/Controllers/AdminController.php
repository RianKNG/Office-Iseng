<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Letter;
use App\Models\Disposisi;
use App\Models\Template;
use App\Models\Cabang;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function dashboard()
{
    $stats = [
        'total_users'         => User::count(),
        'users_aktif'         => User::where('status', 'aktif')->count(),
        'total_surat'         => Letter::count(),
        'surat_bulan_ini'     => Letter::whereMonth('created_at', now()->month)->count(),
        'total_disposisi'     => Disposisi::count(),
        // ✅ FIX: Ganti Disposisi-> jadi Disposisi::
        'disposisi_pending'   => Disposisi::where('status', 'pending')->count(),
    ];

    $recentActivities = Disposisi::with(['letter', 'dari', 'ke'])
        ->latest()
        ->take(10)
        ->get();

    return view('admin.dashboard', compact('stats', 'recentActivities'));
}

       

    // 👥 LIST USER + FILTER DINAMIS
    public function users(Request $request)
    {
        $query = User::with(['cabang', 'jabatan']); // 🔹 BELAJAR ALUR: Eager load relasi agar tidak N+1 query

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('jabatan', function($j) use ($search) {
                      $j->where('nama_jabatan', 'like', "%{$search}%");
                  });
            });
        }

        // 🔹 BELAJAR ALUR: Filter struktur sekarang pakai relasi cabang->tipe
        if ($request->filled('tipe_struktur')) {
            $query->whereHas('cabang', function($c) use ($request) {
                $c->where('tipe', $request->tipe_struktur); // 'pusat', 'cabang', atau 'unit'
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $users = $query->orderByRaw("
            CASE level 
                WHEN 'admin' THEN 7 WHEN 'dirut' THEN 6 WHEN 'kabag' THEN 5 
                WHEN 'kacab' THEN 5 WHEN 'kasubag' THEN 3 WHEN 'kasie' THEN 3 WHEN 'staff' THEN 1 ELSE 0 
            END DESC
        ")->orderBy('nama_lengkap')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        // 🔹 BELAJAR ALUR: Dropdown sekarang ambil dari tabel master, bukan enum statis
        $cabangs = Cabang::orderBy('tipe')->orderBy('nama_cabang')->get();
        $jabatans = Jabatan::orderBy('urutan', 'desc')->get();
        return view('admin.users.create', compact('cabangs', 'jabatans'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'username'     => 'required|unique:users|min:3|max:50',
            'password'     => 'required|min:6|confirmed',
            'nama_lengkap' => 'required|max:100',
            'email'        => 'required|email|unique:users|max:100',
            'level'        => 'required|in:admin,dirut,kabag,kacab,kasubag,kasie,staff', // 🔹 Fixed enum
            'cabang_id'    => 'required|exists:cabangs,id', // 🔹 Ganti struktur -> FK
            'jabatan_id'   => 'required|exists:jabatans,id', // 🔹 Ganti jabatan text -> FK
            'status'       => 'required|in:aktif,nonaktif',
            'no_hp'        => 'nullable|max:20',
            'nik'          => 'nullable|max:50',
        ]);

        DB::beginTransaction();
        try {
            User::create([
                'username'     => $validated['username'],
                'password_hash'=> Hash::make($validated['password']),
                'nama_lengkap' => $validated['nama_lengkap'],
                'email'        => $validated['email'],
                'level'        => $validated['level'],
                'cabang_id'    => $validated['cabang_id'],
                'jabatan_id'   => $validated['jabatan_id'],
                'status'       => $validated['status'],
                'no_hp'        => $validated['no_hp'] ?? null,
                'nik'          => $validated['nik'] ?? null,
            ]);

            DB::commit();
            return redirect()->route('admin.users')->with('success', '✅ User berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create User Error: ' . $e->getMessage());
            return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    public function editUser($id)
    {
        $user = User::with(['cabang', 'jabatan'])->findOrFail($id);
        $cabangs = Cabang::orderBy('tipe')->orderBy('nama_cabang')->get();
        $jabatans = Jabatan::orderBy('urutan', 'desc')->get();
        return view('admin.users.edit', compact('user', 'cabangs', 'jabatans'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'username'     => 'required|min:3|max:50|unique:users,username,'.$user->id,
            'nama_lengkap' => 'required|max:100',
            'email'        => 'required|email|max:100|unique:users,email,'.$user->id,
            'level'        => 'required|in:admin,dirut,kabag,kacab,kasubag,kasie,staff',
            'cabang_id'    => 'required|exists:cabangs,id',
            'jabatan_id'   => 'required|exists:jabatans,id',
            'status'       => 'required|in:aktif,nonaktif',
            'no_hp'        => 'nullable|max:20',
            'nik'          => 'nullable|max:50',
            'password'     => 'nullable|min:6|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'username'     => $validated['username'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'email'        => $validated['email'],
                'level'        => $validated['level'],
                'cabang_id'    => $validated['cabang_id'],
                'jabatan_id'   => $validated['jabatan_id'],
                'status'       => $validated['status'],
                'no_hp'        => $validated['no_hp'] ?? null,
                'nik'          => $validated['nik'] ?? null,
            ];

            if (!empty($validated['password'])) {
                $updateData['password_hash'] = Hash::make($validated['password']);
            }

            $user->update($updateData);
            DB::commit();
            return redirect()->route('admin.users')->with('success', '✅ User berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', '❌ Tidak dapat menghapus akun sendiri');
        }
        if ($user->level === 'admin' && User::where('level', 'admin')->count() <= 1) {
            return back()->with('error', '❌ Harus ada minimal 1 user admin');
        }

        DB::beginTransaction();
        try {
            $user->update(['status' => 'nonaktif']); // Soft delete aman
            DB::commit();
            return back()->with('success', '✅ User berhasil dinonaktifkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    // 📄 MANAJEMEN SURAT - READ ONLY (Admin lihat semua)
    public function letters(Request $request)
    {
        $query = Letter::with(['template', 'creator', 'disposisis']);
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                  ->orWhere('perihal', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $letters = $query->latest()->paginate(20);
        return view('admin.letters', compact('letters'));
    }

    // 🔄 MANAJEMEN DISPOSISI - READ ONLY
    public function disposisi(Request $request)
    {
        $query = Disposisi::with(['letter', 'dari', 'ke']);
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('letter', function($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $disposisis = $query->latest()->paginate(20);
        return view('admin.disposisi', compact('disposisis'));
    }

    // ⚙️ MANAJEMEN TEMPLATE (Opsional)
    public function templates()
    {
        $templates = Template::with('fields')->latest()->paginate(15);
        return view('admin.templates', compact('templates'));
    }
}