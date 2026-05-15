@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Cabang Baru</h5>
                    <a href="{{ route('admin.cabangs') }}" class="btn btn-light btn-sm">← Kembali</a>
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

                    <form method="POST" action="{{ route('admin.cabangs.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Cabang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_cabang" class="form-control @error('nama_cabang') is-invalid @enderror" 
                                   value="{{ old('nama_cabang') }}" required placeholder="Contoh: Cabang Bandung">
                            @error('nama_cabang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Nama lengkap cabang/kantor</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Kode <span class="text-danger">*</span></label>
                            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" 
                                   value="{{ old('kode') }}" required placeholder="Contoh: CAB-BDG" maxlength="20">
                            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Kode unik (huruf kapital, tanpa spasi)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipe <span class="text-danger">*</span></label>
                            <select name="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="pusat" {{ old('tipe')=='pusat'?'selected':'' }}>🏢 Pusat (Kantor Utama)</option>
                                <option value="cabang" {{ old('tipe')=='cabang'?'selected':'' }}>🏪 Cabang (Regional)</option>
                                <option value="unit" {{ old('tipe')=='unit'?'selected':'' }}>🏭 Unit (Operasional)</option>
                            </select>
                            @error('tipe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Pilih level kantor</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat</label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" 
                                      rows="3" placeholder="Alamat lengkap cabang">{{ old('alamat') }}</textarea>
                            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('admin.cabangs') }}" class="btn btn-secondary me-md-2">Batal</a>
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