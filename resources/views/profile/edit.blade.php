@extends('layouts.app')

@section('content')
@push('styles')
<style>
    /* ID Card Style - Ukuran Kartu ATM (85.6mm x 53.98mm) */
    .id-card {
        width: 350px;
        height: 220px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        position: relative;
        color: white;
        margin: 0 auto;
        font-family: 'Poppins', sans-serif;
    }

    .id-card-header {
        background: rgba(255, 255, 255, 0.2);
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255,255,255,0.3);
    }

    .id-card-logo {
        font-size: 24px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .id-card-body {
        padding: 20px;
        display: flex;
        gap: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .id-card-photo {
        width: 90px;
        height: 90px;
        border-radius: 10px;
        border: 3px solid white;
        object-fit: cover;
        background: rgb(84, 89, 134);
    }

    .id-card-info {
        flex: 1;
        text-align: left;
    }

    .id-card-name {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .id-card-nip {
        font-size: 12px;
        opacity: 0.9;
        margin-bottom: 10px;
    }

    .id-card-jabatan {
        font-size: 13px;
        background: rgba(255, 255, 255, 0.2);
        padding: 5px 10px;
        border-radius: 5px;
        display: inline-block;
    }

    .id-card-footer {
        position: absolute;
        bottom: 10px;
        left: 20px;
        right: 20px;
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        opacity: 0.8;
    }

    .id-card-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        font-weight: bold;
        border-radius: 15px;
    }

    .id-card-wrapper {
        position: sticky;
        top: 20px;
    }
</style>

@endpush

<div class="container py-4">
    <div class="row">
        <!-- Left Column: Forms -->
        <div class="col-lg-8">
            <!-- Profil Pengguna -->
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
                            <input type="text" name="name" class="form-control" 
                                   value="{{ old('name', auth()->user()->nama_lengkap) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="{{ old('email', auth()->user()->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control" 
                                   value="{{ old('nip', auth()->user()->nip) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" 
                                   value="{{ old('jabatan', auth()->user()->jabatan) }}">
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

        <!-- Right Column: ID Card -->
        <div class="col-lg-4">
            <div class="id-card-wrapper">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">🆔 ID Card Pegawai</h5>
                    </div>
                    <div class="card-body text-center">
                        @if(auth()->user()->foto_profile)
                            <!-- ID Card dengan Foto -->
                            <div class="id-card">
                                <div class="id-card-header">
                                    <div class="id-card-logo">🏢 E-OFFICE</div>
                                    <div style="font-size: 12px;">PDAM</div>
                                </div>
                                <div class="id-card-body">
                                    <img src="{{ asset('storage/' . auth()->user()->foto_profile) }}" 
                                         alt="Foto" 
                                         class="id-card-photo">
                                    <div class="id-card-info">
                                        <div class="id-card-name">{{ auth()->user()->nama_lengkap }}</div>
                                        <div class="id-card-nip">NIP: {{ auth()->user()->nik ?? '-' }}</div>
                                        <div class="id-card-jabatan">{{ auth()->user()->jabatan ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="id-card-footer">
                                    <span>ID: {{ str_pad(auth()->user()->id, 6, '0', STR_PAD_LEFT) }}</span>
                                    <span>{{ date('Y') }}</span>
                                </div>
                            </div>
                        @else
                            <!-- ID Card Tanpa Foto (Placeholder) -->
                            <div class="id-card">
                                <div class="id-card-placeholder" style="border-radius: 15px; height: 220px;">
                                    <div class="text-center p-4">
                                        <div style="font-size: 60px; margin-bottom: 10px;">👤</div>
                                        <div style="font-size: 14px; font-weight: bold;">{{ auth()->user()->nama_lengkap }}</div>
                                        <div style="font-size: 12px; opacity: 0.9;">{{ auth()->user()->jabatan ?? '-' }}</div>
                                        <div style="font-size: 11px; margin-top: 5px;">NIP: {{ auth()->user()->nik ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Upload foto profil untuk menampilkan ID Card
                            </small>
                        </div>
                         <!-- Tombol Download -->
                            <button class="btn btn-success btn-sm mt-3 w-100" onclick="downloadIDCard()">
                                <i class="bi bi-download"></i> Download ID Card
                            </button>

                        <!-- Upload Foto Profil -->
                        <hr class="my-3">
                        <form action="{{ route('profile.upload-photo') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small">Upload Foto Profil</label>
                                <input type="file" name="foto_profile" class="form-control form-control-sm" 
                                       accept=".png,.jpg,.jpeg" required>
                                <small class="text-muted">Max: 2MB</small>
                            </div>
                            <button type="submit" class="btn btn-info btn-sm w-100">
                                <i class="bi bi-camera"></i> Upload Foto
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">📋 Informasi Akun</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <small class="text-muted">Level Akses:</small><br>
                                <span class="badge bg-primary">{{ auth()->user()->role ?? 'Staff' }}</span>
                            </li>
                            <li class="mb-2">
                                <small class="text-muted">Status:</small><br>
                                <span class="badge bg-success">Aktif</span>
                            </li>
                            <li class="mb-2">
                                <small class="text-muted">Bergabung:</small><br>
                                <strong>{{ auth()->user()->created_at ? auth()->user()->created_at->format('d M Y') : '-' }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
       

</div>
@endsection
<!-- Script untuk Download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
function downloadIDCard() {
    const card = document.getElementById("id-card");
    html2canvas(card, { scale: 2 }).then(canvas => {
        const link = document.createElement("a");
        link.download = "IDCard.png";
        link.href = canvas.toDataURL("image/png");
        link.click();
    });
}
</script>
