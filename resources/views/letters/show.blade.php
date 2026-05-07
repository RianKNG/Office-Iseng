@extends('layouts.app')

@section('content')
<!-- Tambahkan tag style di bawah ini -->
<style>
    @media print {
        /* Sembunyikan tombol, sidebar, dan navbar saat diprint */
        .btn, .sidebar, .navbar, .footer, .btn-primary, .btn-success {
            display: none !important;
        }
        
        /* Opsional: Memastikan konten surat melebar penuh saat print */
        .main-content, .container {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text text-primary"></i> Detail Surat
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('letters.index') }}">Surat</a></li>
                    <li class="breadcrumb-item active">{{ $letter->nomor_surat }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('letters.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('letters.pdf', $letter->id) }}" class="btn btn-danger btn-sm" target="_blank">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Kolom Kiri: Detail Surat -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Informasi Surat</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nomor Surat</th>
                            <td><strong>{{ $letter->nomor_surat }}</strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal Surat</th>
                            <td>{{ $letter->tanggal->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Perihal</th>
                            <td>{{ $letter->perihal }}</td>
                        </tr>
                        <tr>
                            <th>Jenis</th>
                            <td>
                                @php
                                    $jenisBadge = [
                                        'masuk' => 'info',
                                        'keluar' => 'success',
                                        'nota' => 'warning'
                                    ][$letter->jenis];
                                    $jenisLabel = [
                                        'masuk' => 'Surat Masuk',
                                        'keluar' => 'Surat Keluar',
                                        'nota' => 'Nota Dinas'
                                    ][$letter->jenis];
                                @endphp
                                <span class="badge bg-{{ $jenisBadge }}">{{ $jenisLabel }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Template</th>
                            <td>{{ $letter->template->nama_template }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @php
                                    $statusBadge = [
                                        'draft' => 'secondary',
                                        'menunggu_verifikasi' => 'warning',
                                        'disetujui' => 'success',
                                        'ditolak' => 'danger',
                                        'arsip' => 'dark'
                                    ][$letter->status];
                                    $statusLabel = [
                                        'draft' => 'Draft',
                                        'menunggu_verifikasi' => 'Menunggu Verifikasi',
                                        'disetujui' => 'Disetujui',
                                        'ditolak' => 'Ditolak',
                                        'arsip' => 'Arsip'
                                    ][$letter->status];
                                @endphp
                                <span class="badge bg-{{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>
                                <strong>{{ $letter->creator->nama_lengkap }}</strong><br>
                                <small class="text-muted">{{ $letter->creator->jabatan }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Dibuat</th>
                            
                          <td>{{ $letter->created_at ? $letter->created_at->diffForHumans() : 'Tanggal tidak tersedia' }} WIB</td>
                        </tr>
                    </table>

                    <!-- Detail Isi Surat -->
                    @if($letter->values->count() > 0)
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">📋 Detail Isi Surat</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                @foreach($letter->values as $val)
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
                    @if($letter->file_path)
                    <hr class="my-4">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-paperclip me-2 fs-4"></i>
                        <div class="flex-grow-1">
                            <strong>Lampiran File</strong><br>
                            <small>{{ pathinfo($letter->file_path, PATHINFO_EXTENSION) }} - {{ number_format(filesize(storage_path('app/public/' . $letter->file_path))/1024, 2) }} KB</small>
                        </div>
                        <a href="{{ asset('storage/' . $letter->file_path) }}" 
                           class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Disposisi -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-inbox"></i> Disposisi</h6>
                    <span class="badge bg-primary">{{ $letter->disposisis->count() }}</span>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @if($letter->disposisis->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($letter->disposisis as $disp)
                            <div class="list-group-item list-group-item-action py-3">
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="fw-bold text-primary">
                                        {{ $disp->dari->nama_lengkap }}
                                    </small>
                                    <small class="text-muted">
    {{ $disp->created_at ? $disp->created_at->diffForHumans() : 'Baru saja' }}
</small>
                                </div>
                                
                                <p class="mb-1 small">
                                    <i class="bi bi-arrow-right"></i> 
                                    {{ $disp->ke->nama_lengkap }}
                                </p>
                                <p class="mb-1 small fst-italic text-muted">
                                    "{{ Str::limit($disp->instruksi, 60) }}"
                                </p>
                                <div>
                                    <span class="badge bg-{{ $disp->prioritas == 'segera' ? 'danger' : ($disp->prioritas == 'penting' ? 'warning' : 'secondary') }} me-1">
                                        {{ ucfirst($disp->prioritas) }}
                                    </span>
                                    <span class="badge bg-{{ $disp->status == 'selesai' ? 'success' : 'info' }}">
                                        {{ ucfirst($disp->status) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Belum ada disposisi
                        </p>
                    @endif
                </div>
            </div>

            <!-- Preview Surat -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-eye"></i> Preview</h6>
                </div>
                <div class="card-body text-center">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#previewModal">
                        <i class="bi bi-eye"></i> Lihat Preview
                    </button>
                </div>
            </div>
        </div>
    </div>


<!-- Modal Preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">👁️ Preview Surat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="preview-content" class="p-4 bg-white" style="min-height: 400px;">
                    @include('letters._preview_content')
                </div>
            </div>
            <div class="modal-footer">
                
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Halaman
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="{{ route('letters.pdf', $letter->id) }}" class="btn btn-danger" target="_blank">
                    <i class="bi bi-file-pdf"></i> Download PDF
                </a>
            </div>
        </div>
    </div>
</div>
@endsection