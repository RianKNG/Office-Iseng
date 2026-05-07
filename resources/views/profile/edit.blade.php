@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">👤 Profil Pengguna</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                        </div>

                        {{-- <div class="mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip', auth()->user()->nip) }}">
                        </div> --}}

                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', auth()->user()->jabatan) }}">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Upload Signature -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">✍️ Tanda Tangan Digital</h5>
                </div>
                <div class="card-body">
                    @if(auth()->user()->signature_path)
                    <div class="text-center mb-4 p-3 bg-light rounded">
                        <p class="text-muted mb-3">Tanda Tangan Saat Ini:</p>
                        <img src="{{ asset('storage/' . auth()->user()->signature_path) }}" 
                             alt="Signature" 
                             class="img-fluid" 
                             style="max-height: 120px; border: 1px solid #ddd; padding: 15px; background: white;">
                    </div>

                    <form action="{{ route('profile.remove-signature') }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus tanda tangan?')">
                            <i class="bi bi-trash"></i> Hapus Tanda Tangan
                        </button>
                    </form>
                    <hr class="my-3">
                    @endif

                    <form action="{{ route('profile.upload-signature') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Upload Tanda Tangan</label>
                            <input type="file" name="signature" class="form-control @error('signature') is-invalid @enderror" 
                                   accept=".png,.jpg,.jpeg" required>
                            <small class="text-muted">
                                Format: PNG/JPG, Max: 2MB<br>
                                💡 Tips: Gunakan background transparan (PNG) untuk hasil terbaik
                            </small>
                            @error('signature')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload"></i> Upload Tanda Tangan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection