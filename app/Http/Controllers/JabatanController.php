<?php
namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JabatanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // 🔹 READ: Tampilkan daftar jabatan
    public function index(Request $request)
    {
        $query = Jabatan::query();
        
        if ($request->filled('search')) {
            $query->where('nama_jabatan', 'like', '%'.$request->search.'%')
                  ->orWhere('level_key', 'like', '%'.$request->search.'%');
        }
        
        if ($request->filled('scope')) {
            $query->where('scope', $request->scope);
        }
        
        $jabatans = $query->orderBy('urutan', 'desc')->orderBy('nama_jabatan')->paginate(15);
        
        return view('admin.jabatans.index', compact('jabatans'));
    }

    // 🔹 CREATE: Tampilkan form tambah jabatan
    public function create()
    {
        return view('admin.jabatans.create');
    }

    // 🔹 STORE: Simpan data jabatan baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:100',
            'level_key'    => 'required|in:admin,dirut,kabag,kacab,kanit,kasubag,kasie,staff',
            'urutan'       => 'required|integer|min:1|max:10',
            'scope'        => 'required|in:pusat,cabang,unit,semua',
        ]);
        
        DB::beginTransaction();
        try {
            Jabatan::create($validated);
            DB::commit();
            return redirect()->route('admin.jabatans')->with('success', '✅ Jabatan berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    // 🔹 EDIT: Tampilkan form edit jabatan
    public function edit($id)
    {
        $jabatan = Jabatan::findOrFail($id);
        return view('admin.jabatans.edit', compact('jabatan'));
    }

    // 🔹 UPDATE: Update data jabatan
    public function update(Request $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);
        
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:100',
            'level_key'    => 'required|in:admin,dirut,kabag,kacab,kanit,kasubag,kasie,staff',
            'urutan'       => 'required|integer|min:1|max:10',
            'scope'        => 'required|in:pusat,cabang,unit,semua',
        ]);
        
        DB::beginTransaction();
        try {
            $jabatan->update($validated);
            DB::commit();
            return redirect()->route('admin.jabatans')->with('success', '✅ Jabatan berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    // 🔹 DELETE: Hapus jabatan
    public function destroy($id)
    {
        $jabatan = Jabatan::findOrFail($id);
        
        // 🔹 BELAJAR: Cek apakah jabatan masih dipakai user
        $usedByUsers = \App\Models\User::where('jabatan_id', $id)->exists();
        if ($usedByUsers) {
            return back()->with('error', '❌ Tidak dapat menghapus: Jabatan masih digunakan oleh user');
        }
        
        DB::beginTransaction();
        try {
            $jabatan->delete(); // Hard delete aman karena tidak ada relasi aktif
            DB::commit();
            return back()->with('success', '✅ Jabatan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }
}