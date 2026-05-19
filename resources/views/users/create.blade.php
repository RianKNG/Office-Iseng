@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">➕ Tambah User Baru</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">User</a></li>
                    <li class="breadcrumb-item active">Tambah</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Error Alert -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Terjadi {{ count($errors) }} error:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">📋 Informasi User</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" id="formCreateUser">
                @csrf
                
                <div class="row g-3">
                    <!-- NIK -->
                    <div class="col-md-3">
                        <label for="nik" class="form-label">NIK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nik') is-invalid @enderror" 
                               id="nik" name="nik" value="{{ old('nik') }}" required maxlength="20">
                        @error('nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div class="col-md-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                               id="username" name="username" value="{{ old('username') }}" required maxlength="50">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-md-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required maxlength="100">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="col-md-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required minlength="6">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="col-md-4">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" 
                               id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required maxlength="100">
                        @error('nama_lengkap')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- No. HP -->
                    <div class="col-md-4">
                        <label for="no_hp" class="form-label">No. HP</label>
                        <input type="text" class="form-control @error('no_hp') is-invalid @enderror" 
                               id="no_hp" name="no_hp" value="{{ old('no_hp') }}" maxlength="20">
                        @error('no_hp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Jabatan -->
                    <div class="col-md-4">
                        <label for="jabatan" class="form-label">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('jabatan') is-invalid @enderror" 
                               id="jabatan" name="jabatan" value="{{ old('jabatan') }}" required maxlength="100">
                        @error('jabatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Struktur -->
                    <div class="col-md-3">
                        <label for="struktur" class="form-label">Struktur <span class="text-danger">*</span></label>
                        <select class="form-select @error('struktur') is-invalid @enderror" 
                                id="struktur" name="struktur" required>
                            <option value="">Pilih...</option>
                            <option value="pusat" {{ old('struktur') == 'pusat' ? 'selected' : '' }}>Pusat</option>
                            <option value="cabang" {{ old('struktur') == 'cabang' ? 'selected' : '' }}>Cabang</option>
                        </select>
                        @error('struktur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Level -->
                    <div class="col-md-3">
                        <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                        <select class="form-select @error('level') is-invalid @enderror" 
                                id="level" name="level" required>
                            <option value="">Pilih...</option>
                            <option value="admin" {{ old('level') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="dirut" {{ old('level') == 'dirut' ? 'selected' : '' }}>Direktur Utama</option>
                            <option value="kabag" {{ old('level') == 'kabag' ? 'selected' : '' }}>Kabag</option>
                            <option value="kasubag" {{ old('level') == 'kasubag' ? 'selected' : '' }}>Kasubag</option>
                            <option value="kasie" {{ old('level') == 'kasie' ? 'selected' : '' }}>Kasie</option>
                            <option value="staff" {{ old('level') == 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                        @error('level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Unit Kerja -->
                    <div class="col-md-3">
                        <label for="unit_kerja" class="form-label">Unit Kerja <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('unit_kerja') is-invalid @enderror" 
                               id="unit_kerja" name="unit_kerja" value="{{ old('unit_kerja') }}" 
                               placeholder="keuangan, pelayanan, sdm, dll" required maxlength="50">
                        <small class="text-muted">Contoh: keuangan, pelayanan, sdm, tekprod</small>
                        @error('unit_kerja')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                            <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Foto Profile -->
                    <div class="col-md-6">
                        <label for="foto_profile" class="form-label">Foto Profile</label>
                        <input type="file" class="form-control @error('foto_profile') is-invalid @enderror" 
                               id="foto_profile" name="foto_profile" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                        @error('foto_profile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <img id="previewFoto" class="mt-2 rounded border" style="max-height: 100px; display: none;">
                    </div>

                    <!-- Tanda Tangan Digital -->
                    <div class="col-md-6">
                        <label for="signature" class="form-label">Tanda Tangan Digital</label>
                        <input type="file" class="form-control @error('signature') is-invalid @enderror" 
                               id="signature" name="signature" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                        @error('signature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <img id="previewSignature" class="mt-2 rounded border" style="max-height: 100px; display: none;">
                    </div>
                </div>

                <hr class="my-4">

                <!-- Buttons -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">
                        <i class="bi bi-save"></i> <span id="btnText">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript untuk Preview Image & Loading -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview Foto Profile
    const fotoInput = document.getElementById('foto_profile');
    const previewFoto = document.getElementById('previewFoto');
    
    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            previewImage(e.target, previewFoto);
        });
    }

    // Preview Signature
    const sigInput = document.getElementById('signature');
    const previewSig = document.getElementById('previewSignature');
    
    if (sigInput) {
        sigInput.addEventListener('change', function(e) {
            previewImage(e.target, previewSig);
        });
    }

    // Loading state saat submit
    const form = document.getElementById('formCreateUser');
    const btnSimpan = document.getElementById('btnSimpan');
    const btnText = document.getElementById('btnText');

    if (form && btnSimpan) {
        form.addEventListener('submit', function() {
            btnSimpan.disabled = true;
            btnText.textContent = 'Menyimpan...';
            btnSimpan.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...';
        });
    }
});

// Helper function untuk preview image
function previewImage(input, previewElement) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewElement.src = e.target.result;
            previewElement.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewElement.style.display = 'none';
    }
}
</script>
@endpush
@endsection