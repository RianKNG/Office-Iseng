<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Letter;
use App\Models\Disposisi;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']); // Pastikan middleware 'admin' sudah didaftarkan
    }

    // 📊 Dashboard Admin
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'users_aktif' => User::where('status', 'aktif')->count(),
            'total_surat' => Letter::count(),
            'surat_bulan_ini' => Letter::whereMonth('created_at', now()->month)->count(),
            'total_disposisi' => Disposisi::count(),
            'disposisi_pending' => Disposisi::where('status', 'pending')->count(),
        ];
        
        $recentActivities = Disposisi::with(['letter', 'dari', 'ke'])
            ->latest()
            ->take(10)
            ->get();
        
        return view('admin.dashboard', compact('stats', 'recentActivities'));
    }

    // 👥 MANAJEMEN USER - CRUD
    public function users(Request $request)
    {
        $query = User::query();
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }
        
        // Filter
        if ($request->has('struktur')) {
            $query->where('struktur', $request->struktur);
        }
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }
        
        $users = $query->orderBy('level_urutan', 'desc')
                      ->orderBy('nama_lengkap')
                      ->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:users|min:3|max:50',
            'password' => 'required|min:6|confirmed',
            'nama_lengkap' => 'required|max:100',
            'email' => 'required|email|unique:users|max:100',
            'jabatan' => 'required|max:100',
            'level' => 'required|in:admin,dirut,kabag_kacab,kasubag_kasie,staff',
            'struktur' => 'required|in:pusat,cabang',
            'unit_kerja' => 'required|in:keuangan,pelayanan,teknikprod,perencanaan,umum',
            'status' => 'required|in:aktif,nonaktif',
            'no_hp' => 'nullable|max:20',
            'nik' => 'nullable|max:50',
        ]);

        DB::beginTransaction();
        try {
            User::create([
                'username' => $validated['username'],
                'password_hash' => Hash::make($validated['password']),
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'jabatan' => $validated['jabatan'],
                'level' => $validated['level'],
                'struktur' => $validated['struktur'],
                'unit_kerja' => $validated['unit_kerja'],
                'status' => $validated['status'],
                'no_hp' => $validated['no_hp'] ?? null,
                'nik' => $validated['nik'] ?? null,
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
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'username' => 'required|min:3|max:50|unique:users,username,'.$user->id,
            'nama_lengkap' => 'required|max:100',
            'email' => 'required|email|max:100|unique:users,email,'.$user->id,
            'jabatan' => 'required|max:100',
            'level' => 'required|in:admin,dirut,kabag_kacab,kasubag_kasie,staff',
            'struktur' => 'required|in:pusat,cabang',
            'unit_kerja' => 'required|in:keuangan,pelayanan,teknikprod,perencanaan,umum',
            'status' => 'required|in:aktif,nonaktif',
            'no_hp' => 'nullable|max:20',
            'nik' => 'nullable|max:50',
            'password' => 'nullable|min:6|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'username' => $validated['username'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'jabatan' => $validated['jabatan'],
                'level' => $validated['level'],
                'struktur' => $validated['struktur'],
                'unit_kerja' => $validated['unit_kerja'],
                'status' => $validated['status'],
                'no_hp' => $validated['no_hp'] ?? null,
                'nik' => $validated['nik'] ?? null,
            ];
            
            // Update password hanya jika diisi
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
        
        // Prevent delete self or last admin
        if ($user->id === auth()->id()) {
            return back()->with('error', '❌ Tidak dapat menghapus akun sendiri');
        }
        
        if ($user->level === 'admin' && User::where('level', 'admin')->count() <= 1) {
            return back()->with('error', '❌ Harus ada minimal 1 user admin');
        }
        
        DB::beginTransaction();
        try {
            // Soft delete: ubah status jadi nonaktif (lebih aman)
            $user->update(['status' => 'nonaktif']);
            // Atau hard delete: $user->delete();
            
            DB::commit();
            return back()->with('success', '✅ User berhasil dihapus');
            
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