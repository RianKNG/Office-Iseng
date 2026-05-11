@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>➕ Tambah User Baru</h4>
        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">👤 Informasi Akun</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" 
                                   value="{{ old('username') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                            <small class="text-muted">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" 
                                   value="{{ old('email') }}" required>
                        </div>
                    </div>
                    
                    <!-- Kolom Kanan -->
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">🏢 Informasi Jabatan</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" class="form-control" 
                                   value="{{ old('nama_lengkap') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jabatan *</label>
                            <input type="text" name="jabatan" class="form-control" 
                                   value="{{ old('jabatan') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Level *</label>
                            <select name="level" class="form-select" required>
                                <option value="">-- Pilih Level --</option>
                                <option value="admin" {{ old('level')=='admin'?'selected':'' }}>Administrator</option>
                                <option value="dirut" {{ old('level')=='dirut'?'selected':'' }}>Direktur Utama</option>
                                <option value="kabag_kacab" {{ old('level')=='kabag_kacab'?'selected':'' }}>Kabag / Kacab</option>
                                <option value="kasubag_kasie" {{ old('level')=='kasubag_kasie'?'selected':'' }}>Kasubag / Kasie</option>
                                <option value="staff" {{ old('level')=='staff'?'selected':'' }}>Staff</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Struktur *</label>
                            <select name="struktur" class="form-select" required>
                                <option value="">-- Pilih Struktur --</option>
                                <option value="pusat" {{ old('struktur')=='pusat'?'selected':'' }}>Pusat</option>
                                <option value="cabang" {{ old('struktur')=='cabang'?'selected':'' }}>Cabang</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Unit Kerja *</label>
                            <select name="unit_kerja" class="form-select" required>
                                <option value="">-- Pilih Unit --</option>
                                <option value="keuangan" {{ old('unit_kerja')=='keuangan'?'selected':'' }}>Keuangan</option>
                                <option value="pelayanan" {{ old('unit_kerja')=='pelayanan'?'selected':'' }}>Pelayanan</option>
                                <option value="teknikprod" {{ old('unit_kerja')=='teknikprod'?'selected':'' }}>Teknik & Produksi</option>
                                <option value="perencanaan" {{ old('unit_kerja')=='perencanaan'?'selected':'' }}>Perencanaan</option>
                                <option value="umum" {{ old('unit_kerja')=='umum'?'selected':'' }}>Umum</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="aktif" {{ old('status')=='aktif'?'selected':'' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status')=='nonaktif'?'selected':'' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection