<?php
namespace App\Http\Controllers;

use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CabangController extends Controller
{
    public function __construct()
    {
        // 🔹 BELAJAR: Middleware agar hanya admin yang bisa akses
        $this->middleware(['auth', 'admin']);
    }

    // 🔹 READ: Tampilkan daftar cabang
    public function index(Request $request)
    {
        $query = Cabang::query();
        
        // 🔹 BELAJAR: Filter search sederhana
        if ($request->filled('search')) {
            $query->where('nama_cabang', 'like', '%'.$request->search.'%')
                  ->orWhere('kode', 'like', '%'.$request->search.'%');
        }
        
        // 🔹 BELAJAR: Filter by tipe (pusat/cabang/unit)
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        
        $cabangs = $query->orderBy('tipe')->orderBy('nama_cabang')->paginate(15);
        
        return view('admin.cabangs.index', compact('cabangs'));
    }

    // 🔹 CREATE: Tampilkan form tambah cabang
    public function create()
    {
        return view('admin.cabangs.create');
    }

    // 🔹 STORE: Simpan data cabang baru
    public function store(Request $request)
    {
        // 🔹 BELAJAR: Validasi input
        $validated = $request->validate([
            'nama_cabang' => 'required|string|max:100|unique:cabangs,nama_cabang',
            'kode'        => 'required|string|max:20|unique:cabangs,kode',
            'tipe'        => 'required|in:pusat,cabang,unit',
            'alamat'      => 'nullable|string|max:255',
        ]);
        
        DB::beginTransaction();
        try {
            Cabang::create($validated);
            DB::commit();
            return redirect()->route('admin.cabangs')->with('success', '✅ Cabang berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    // 🔹 EDIT: Tampilkan form edit cabang
    public function edit($id)
    {
        $cabang = Cabang::findOrFail($id);
        return view('admin.cabangs.edit', compact('cabang'));
    }

    // 🔹 UPDATE: Update data cabang
    public function update(Request $request, $id)
    {
        $cabang = Cabang::findOrFail($id);
        
        $validated = $request->validate([
            'nama_cabang' => 'required|string|max:100|unique:cabangs,nama_cabang,'.$cabang->id,
            'kode'        => 'required|string|max:20|unique:cabangs,kode,'.$cabang->id,
            'tipe'        => 'required|in:pusat,cabang,unit',
            'alamat'      => 'nullable|string|max:255',
        ]);
        
        DB::beginTransaction();
        try {
            $cabang->update($validated);
            DB::commit();
            return redirect()->route('admin.cabangs')->with('success', '✅ Cabang berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    // 🔹 DELETE: Hapus cabang (soft delete via status)
    public function destroy($id)
    {
        $cabang = Cabang::findOrFail($id);
        
        // 🔹 BELAJAR: Cek apakah cabang masih dipakai user
        $usedByUsers = \App\Models\User::where('cabang_id', $id)->exists();
        if ($usedByUsers) {
            return back()->with('error', '❌ Tidak dapat menghapus: Cabang masih digunakan oleh user');
        }
        
        DB::beginTransaction();
        try {
            // 🔹 BELAJAR: Soft delete - ubah nama jadi "HAPUS: [nama]"
            $cabang->update([
                'nama_cabang' => 'HAPUS: ' . $cabang->nama_cabang,
                'kode' => 'DELETED_' . $cabang->kode,
            ]);
            // Atau hard delete: $cabang->delete();
            DB::commit();
            return back()->with('success', '✅ Cabang berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }
}