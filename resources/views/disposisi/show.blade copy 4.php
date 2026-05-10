@extends('layouts.app')

@section('title', 'Detail Disposisi')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-inbox-fill text-primary"></i> Detail Disposisi
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('disposisi.inbox') }}">Inbox</a></li>
                    <li class="breadcrumb-item active">Detail #{{ $disposisi->id }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('disposisi.inbox') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Status -->
    @if($disposisi->status == 'pending')
        <div class="alert alert-warning d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong>Disposisi Baru!</strong> Disposisi ini menunggu tindakan Anda.
            </div>
        </div>
    @elseif($disposisi->status == 'dibaca')
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-2"></i>
            <div>
                Disposisi ini sudah Anda baca. Silakan segera diproses.
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Kolom Kiri: Informasi Disposisi & Surat -->
        <div class="col-lg-8">
            
            <!-- Card: Informasi Disposisi -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-envelope-paper"></i> Informasi Disposisi</h5>
                    <span class="badge bg-light text-dark">{{ ucfirst($disposisi->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Dari</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 40px; height: 40px;">
                                    {{ substr($disposisi->dari->nama_lengkap, 0, 1) }}
                                </div>
                                <div>
                                    <strong>{{ $disposisi->dari->nama_lengkap }}</strong><br>
                                    <small class="text-muted">{{ $disposisi->dari->jabatan }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Kepada</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 40px; height: 40px;">
                                    {{ substr($disposisi->ke->nama_lengkap, 0, 1) }}
                                </div>
                                <div>
                                    <strong>{{ $disposisi->ke->nama_lengkap }}</strong><br>
                                    <small class="text-muted">{{ $disposisi->ke->jabatan }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Tanggal Disposisi</label>
                            <div class="fw-bold">{{ $disposisi->created_at->format('d M Y') }}</div>
                            <small class="text-muted">{{ $disposisi->created_at->format('H:i') }} WIB</small>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Prioritas</label>
                            <div>
                                @php
                                    $prioritasBadge = [
                                        'biasa' => 'secondary',
                                        'penting' => 'warning',
                                        'segera' => 'danger',
                                        'rahasia' => 'dark'
                                    ][$disposisi->prioritas];
                                    $prioritasIcon = [
                                        'biasa' => 'circle',
                                        'penting' => 'exclamation-triangle',
                                        'segera' => 'lightning-fill',
                                        'rahasia' => 'lock-fill'
                                    ][$disposisi->prioritas];
                                @endphp
                                <span class="badge bg-{{ $prioritasBadge }}">
                                    <i class="bi bi-{{ $prioritasIcon }}"></i> {{ ucfirst($disposisi->prioritas) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Deadline</label>
                            <div>
                                @if($disposisi->deadline)
                                    @if($disposisi->deadline < now() && $disposisi->status != 'selesai')
                                        <span class="text-danger fw-bold">
                                            <i class="bi bi-exclamation-octagon"></i> 
                                            {{ $disposisi->deadline->format('d M Y') }} (Terlambat!)
                                        </span>
                                    @else
                                        <span class="text-success">
                                            <i class="bi bi-calendar-check"></i> 
                                            {{ $disposisi->deadline->format('d M Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small mb-1">Instruksi</label>
                            <div class="alert alert-light border mb-0">
                                <p class="mb-0 fst-italic">"{{ $disposisi->instruksi }}"</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Informasi Surat -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Informasi Surat</h5>
                    <a href="{{ route('letters.show', $disposisi->letter->id) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> Lihat Surat
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nomor Surat</th>
                            <td>
                                <strong>{{ $disposisi->letter->nomor_surat }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Surat</th>
                            <td>{{ $disposisi->letter->tanggal ? $disposisi->letter->tanggal->format('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Perihal</th>
                            <td>{{ $disposisi->letter->perihal }}</td>
                        </tr>
                        <tr>
                            <th>Jenis</th>
                            <td>
                                @php
                                    $jenisBadge = [
                                        'masuk' => 'info',
                                        'keluar' => 'success',
                                        'nota' => 'warning'
                                    ][$disposisi->letter->jenis];
                                @endphp
                                <span class="badge bg-{{ $jenisBadge }}">
                                    {{ ucfirst($disposisi->letter->jenis) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Template</th>
                            <td>{{ $disposisi->letter->template->nama_template }}</td>
                        </tr>
                        <tr>
                            <th>Status Surat</th>
                            <td>
                                @php
                                    $statusBadge = [
                                        'draft' => 'secondary',
                                        'menunggu_verifikasi' => 'warning',
                                        'disetujui' => 'success',
                                        'ditolak' => 'danger',
                                        'arsip' => 'dark'
                                    ][$disposisi->letter->status];
                                @endphp
                                <span class="badge bg-{{ $statusBadge }}">
                                    {{ ucfirst(str_replace('_', ' ', $disposisi->letter->status)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>{{ $disposisi->letter->creator->nama_lengkap }}</td>
                        </tr>
                    </table>

                    <!-- Field Dinamis Surat -->
                    @if($disposisi->letter->values && $disposisi->letter->values->count() > 0)
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">📋 Detail Isi Surat</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <tbody>
                                @foreach($disposisi->letter->values as $val)
                                <tr>
                                    <th width="40%" class="bg-light">{{ $val->field->nama_field }}</th>
                                    <td>{{ nl2br(e($val->nilai)) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <!-- Lampiran File -->
                    @if($disposisi->letter->file_path)
                    <hr class="my-4">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-paperclip me-2 fs-5"></i>
                        <div class="flex-grow-1">
                            <strong>Lampiran tersedia</strong><br>
                            <small>Klik tombol di bawah untuk mengunduh</small>
                        </div>
                        <a href="{{ asset('storage/' . $disposisi->letter->file_path) }}" 
                           class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Card: Riwayat Disposisi (Parent) -->
            @if($disposisi->parent_id && $disposisi->parent)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-diagram-3"></i> Disposisi Induk</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <p class="mb-1">
                                <strong>Dari:</strong> {{ $disposisi->parent->dari->nama_lengkap }}
                            </p>
                            <p class="mb-1">
                                <strong>Kepada:</strong> {{ $disposisi->parent->ke->nama_lengkap }}
                            </p>
                            <p class="mb-0">
                                <strong>Instruksi:</strong> 
                                <em class="text-muted">"{{ $disposisi->parent->instruksi }}"</em>
                            </p>
                        </div>
                        <a href="{{ route('disposisi.show', $disposisi->parent->id) }}" 
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Kolom Kanan: Form Aksi -->
        <div class="col-lg-4">
            
            <!-- Card: Proses Disposisi -->
            @if(in_array($disposisi->status, ['pending', 'dibaca']))
            <div class="card shadow-sm border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-gear-fill"></i> Proses Disposisi</h5>
                </div>
                <div class="card-body">
                    
                    <!-- Form Approve -->
                    <form action="{{ route('disposisi.process', $disposisi->id) }}" method="POST" class="mb-3">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Tindak Lanjut</label>
                            <textarea name="instruksi" class="form-control @error('instruksi') is-invalid @enderror" 
                                rows="3" placeholder="Tambahkan catatan atau instruksi tambahan...">{{ old('instruksi') }}</textarea>
                            @error('instruksi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Opsional - akan menggantikan instruksi sebelumnya</small>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-check-circle"></i> Setujui & Teruskan
                        </button>
                        <small class="text-muted d-block text-center">
                            @if(auth()->user()->level == 'dirut')
                                Persetujuan final (Dirut)
                            @else
                                Akan diteruskan ke level berikutnya
                            @endif
                        </small>
                    </form>

                    <hr class="my-3">

                    <!-- Form Reject -->
                    {{-- <form action="{{ route('disposisi.process', $disposisi->id) }}" method="POST" class="mb-3"> --}}
                        <form action="{{ route('disposisi.process', $disposisi->id) }}" method="POST" class="mb-3">
    @csrf
    <input type="hidden" name="action" value="reject">
    <input type="hidden" name="status" value="ditolak">
    
    <div class="mb-3">
        <label class="form-label fw-bold text-danger">Alasan Penolakan</label>
        <textarea name="instruksi" class="form-control" rows="2" 
            placeholder="Alasan surat ditolak..." required></textarea>
    </div>

    <button type="submit" class="btn btn-danger w-100" 
        onclick="return confirm('⚠️ Yakin ingin menolak disposisi ini?\n\nSurat akan ditandai sebagai DITOLAK.')">
        <i class="bi bi-x-circle"></i> Tolak Surat
    </button>
</form>

                    <hr class="my-3">

                    <!-- Button Forward -->
                    <button type="button" class="btn btn-outline-primary w-100" 
                        data-bs-toggle="modal" data-bs-target="#modalForward">
                        <i class="bi bi-share"></i> Teruskan ke User Lain
                    </button>
                    <small class="text-muted d-block text-center mt-1">
                        Teruskan ke rekan kerja (bukan hierarki)
                    </small>

                </div>
            </div>
            @else
            <!-- Status Sudah Diproses -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Status Disposisi</h6>
                </div>
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                       @php $stat = strtolower(trim($disposisi->status)); @endphp
                        <div class="mb-3">
                            @php $stat = strtolower(trim($disposisi->status)); @endphp
<div class="mb-3">
    @if($stat == 'diproses')
        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        <h5 class="mt-3 text-success">Sudah Diproses</h5>
    @elseif($stat == 'diteruskan')
        <i class="bi bi-share-fill text-info" style="font-size: 4rem;"></i>
        <h5 class="mt-3 text-info">Diteruskan</h5>
    @elseif($stat == 'selesai' || $stat == 'disetujui')
        <i class="bi bi-archive-fill text-secondary" style="font-size: 4rem;"></i>
        <h5 class="mt-3 text-secondary">Selesai</h5>
    @elseif($stat == 'ditolak')
        <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
        <h5 class="mt-3 text-danger">Ditolak</h5>
    @else
        <i class="bi bi-hourglass-split text-warning" style="font-size: 4rem;"></i>
        <h5 class="mt-3 text-warning">Menunggu</h5>
    @endif
</div>
                    </div>
                    <p class="text-muted mb-0">Disposisi ini sudah diproses pada<br>
                    <strong>{{ $disposisi->updated_at->format('d M Y H:i') }}</strong></p>
                </div>
            </div>
            @endif

            <!-- Card: Reply Disposisi -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-chat-left-text"></i> Balas Disposisi</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('disposisi.show', $disposisi->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label small">Balasan untuk {{ $disposisi->dari->nama_lengkap }}</label>
                            <textarea name="instruksi" class="form-control" rows="3" 
                                placeholder="Tulis balasan..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Prioritas</label>
                            <select name="prioritas" class="form-select">
                                <option value="biasa">Biasa</option>
                                <option value="penting">Penting</option>
                                <option value="segera">Segera</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-send"></i> Kirim Balasan
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Forward -->
<!-- Modal Forward -->
<div class="modal fade" id="modalForward" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('disposisi.process', $disposisi->id) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="forward">
            <input type="hidden" name="status" value="diteruskan">
            
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-share"></i> Teruskan Disposisi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small>Teruskan disposisi ini ke user lain untuk ditindaklanjuti</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Teruskan Kepada</label>
                        <select name="ke_user_id" class="form-select" required>
                            <option value="">-- Pilih User --</option>
                            @foreach(\App\Models\User::where('status','aktif')
                                ->where('id', '!=', auth()->id())
                                ->orderBy('nama_lengkap')
                                ->get() as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->nama_lengkap }} - {{ $user->jabatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Instruksi Tambahan</label>
                        <textarea name="instruksi" class="form-control" rows="3" 
                            placeholder="Instruksi untuk user tujuan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Teruskan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Kosongkan atau hapus script ini jika tidak ingin alert hilang otomatis
    console.log("Alert mode: Manual close only.");
});
</script>
@endpush