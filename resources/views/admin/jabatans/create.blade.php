@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Jabatan Baru</h5>
                    <a href="{{ route('admin.jabatans') }}" class="btn btn-light btn-sm">← Kembali</a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.jabatans.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jabatan" class="form-control @error('nama_jabatan') is-invalid @enderror" 
                                   value="{{ old('nama_jabatan') }}" required placeholder="Contoh: Kepala Bagian Keuangan">
                            @error('nama_jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Level Key <span class="text-danger">*</span></label>
                            <select name="level_key" class="form-select @error('level_key') is-invalid @enderror" required>
                                <option value="">-- Pilih Level --</option>
                                <option value="admin" {{ old('level_key')=='admin'?'selected':'' }}>🔧 Admin (7)</option>
                                <option value="dirut" {{ old('level_key')=='dirut'?'selected':'' }}>👔 Direktur Utama (6)</option>
                                <option value="kabag" {{ old('level_key')=='kabag'?'selected':'' }}>📋 Kepala Bagian (5)</option>
                                <option value="kacab" {{ old('level_key')=='kacab'?'selected':'' }}>🏪 Kepala Cabang (5)</option>
                                <option value="kanit" {{ old('level_key')=='kanit'?'selected':'' }}>🔍 Kanit (4)</option>
                                <option value="kasubag" {{ old('level_key')=='kasubag'?'selected':'' }}>📁 Kasubag (3)</option>
                                <option value="kasie" {{ old('level_key')=='kasie'?'selected':'' }}>📂 Kasie (3)</option>
                                <option value="staff" {{ old('level_key')=='staff'?'selected':'' }}>👤 Staff (1)</option>
                            </select>
                            @error('level_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Level untuk routing disposisi (angka dalam kurung = urutan hierarki)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Urutan <span class="text-danger">*</span></label>
                            <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" 
                                   value="{{ old('urutan', 1) }}" min="1" max="10" required>
                            @error('urutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Angka untuk sorting (1 = terendah, 10 = tertinggi)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Scope <span class="text-danger">*</span></label>
                            <select name="scope" class="form-select @error('scope') is-invalid @enderror" required>
                                <option value="">-- Pilih Scope --</option>
                                <option value="pusat" {{ old('scope')=='pusat'?'selected':'' }}>🏢 Hanya Pusat</option>
                                <option value="cabang" {{ old('scope')=='cabang'?'selected':'' }}>🏪 Hanya Cabang</option>
                                <option value="unit" {{ old('scope')=='unit'?'selected':'' }}>🏭 Hanya Unit</option>
                                <option value="semua" {{ old('scope')=='semua'?'selected':'' }}>✅ Semua Struktur</option>
                            </select>
                            @error('scope')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Di struktur mana jabatan ini berlaku</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('admin.jabatans') }}" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection