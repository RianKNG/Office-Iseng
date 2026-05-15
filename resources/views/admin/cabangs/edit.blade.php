@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Cabang</h5>
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

                    <form method="POST" action="{{ route('admin.cabangs.update', $cabang->id) }}">
                        @csrf @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Cabang <span class="text-danger">*</span></label>
                            <input type="text" name="nama_cabang" class="form-control @error('nama_cabang') is-invalid @enderror" 
                                   value="{{ old('nama_cabang', $cabang->nama_cabang) }}" required>
                            @error('nama_cabang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Kode <span class="text-danger">*</span></label>
                            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" 
                                   value="{{ old('kode', $cabang->kode) }}" required maxlength="20">
                            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipe <span class="text-danger">*</span></label>
                            <select name="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                <option value="pusat" {{ old('tipe', $cabang->tipe)=='pusat'?'selected':'' }}>🏢 Pusat</option>
                                <option value="cabang" {{ old('tipe', $cabang->tipe)=='cabang'?'selected':'' }}>🏪 Cabang</option>
                                <option value="unit" {{ old('tipe', $cabang->tipe)=='unit'?'selected':'' }}>🏭 Unit</option>
                            </select>
                            @error('tipe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat</label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" 
                                      rows="3">{{ old('alamat', $cabang->alamat) }}</textarea>
                            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('admin.cabangs') }}" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-circle"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection