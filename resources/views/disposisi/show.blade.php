@extends('layouts.app')

@section('title', 'Detail Disposisi')

@section('content')
<div class="container-fluid py-4">
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

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Alert Status untuk User Penerima --}}
    @if($disposisi->ke_user_id == auth()->id())
        @if($disposisi->status == 'pending')
            <div class="alert alert-warning d-flex align-items-center border-0 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><strong>Disposisi Baru!</strong> Silakan baca dan tindak lanjuti pesan ini.</div>
            </div>
        @elseif($disposisi->status == 'dibaca')
            <div class="alert alert-info d-flex align-items-center border-0 shadow-sm">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>Anda telah membaca disposisi ini. Segera berikan respon atau tindak lanjut.</div>
            </div>
        @endif
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Informasi Utama Disposisi --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0"><i class="bi bi-envelope-paper"></i> Informasi Disposisi</h5>
                    <span class="badge bg-white text-primary fw-bold text-uppercase">{{ $disposisi->status }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <label class="text-muted small mb-2 d-block text-uppercase fw-bold">Pengirim</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 45px; height: 45px;">
                                    {{ strtoupper(substr($disposisi->dari->nama_lengkap ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $disposisi->dari->nama_lengkap ?? 'Unknown' }}</h6>
                                    <small class="text-muted">{{ $disposisi->dari->jabatan ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small mb-2 d-block text-uppercase fw-bold">Penerima</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 45px; height: 45px;">
                                    {{ strtoupper(substr($disposisi->ke->nama_lengkap ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $disposisi->ke->nama_lengkap ?? 'Unknown' }}</h6>
                                    <small class="text-muted">{{ $disposisi->ke->jabatan ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">📅 Tanggal Disposisi</label>
                            @if($disposisi->created_at instanceof \Carbon\Carbon)
                                <div class="fw-bold">{{ $disposisi->created_at->translatedFormat('d M Y') }}</div>
                                <small class="text-muted">{{ $disposisi->created_at->format('H:i') }} WIB</small>
                            @else
                                <div class="fw-bold">-</div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted small mb-1">⚡ Prioritas</label>
                            <div>
                                @php
                                    $pConf = [
                                        'biasa' => ['bg' => 'secondary', 'icon' => 'circle'],
                                        'penting' => ['bg' => 'warning', 'icon' => 'exclamation-triangle'],
                                        'segera' => ['bg' => 'danger', 'icon' => 'lightning-fill'],
                                        'rahasia' => ['bg' => 'dark', 'icon' => 'lock-fill'],
                                    ][strtolower($disposisi->prioritas ?? 'biasa')] ?? ['bg' => 'secondary', 'icon' => 'circle'];
                                @endphp
                                <span class="badge bg-{{ $pConf['bg'] }}">
                                    <i class="bi bi-{{ $pConf['icon'] }}"></i> {{ ucfirst($disposisi->prioritas) }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted small mb-1">⏰ Deadline</label>
                            <div>
                                @if($disposisi->deadline instanceof \Carbon\Carbon)
                                    @php $isOverdue = $disposisi->deadline->isPast() && $disposisi->status != 'selesai'; @endphp
                                    <span class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-dark' }}">
                                        <i class="bi {{ $isOverdue ? 'bi-exclamation-octagon' : 'bi-calendar-check' }}"></i> 
                                        {{ $disposisi->deadline->translatedFormat('d M Y') }}
                                        {!! $isOverdue ? '<br><small>(Terlambat!)</small>' : '' !!}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="text-muted small mb-1">📝 Isi Instruksi</label>
                            <div class="p-3 bg-light rounded border-start border-primary border-4">
                                <p class="mb-0 text-dark" style="white-space: pre-line;">{{ $disposisi->instruksi ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Surat Terkait --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2"></i> Dokumen Surat</h6>
                    @if($disposisi->letter)
                    <a href="{{ route('letters.show', $disposisi->letter->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> Detail Lengkap Surat
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($disposisi->letter)
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block text-uppercase">Nomor Surat</small>
                                <span class="fw-bold">{{ $disposisi->letter->nomor_surat }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <small class="text-muted d-block text-uppercase">Perihal</small>
                                <span>{{ $disposisi->letter->perihal }}</span>
                            </div>
                        </div>

                        @if($disposisi->letter->file_path)
                        <div class="alert alert-secondary d-flex align-items-center mt-2 border-0">
                            <i class="bi bi-file-pdf fs-3 me-3 text-danger"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold">File Lampiran</div>
                                <small class="text-muted">Klik tombol untuk melihat dokumen asli</small>
                            </div>
                            <a href="{{ asset('storage/' . $disposisi->letter->file_path) }}" 
                               class="btn btn-primary" target="_blank">
                                <i class="bi bi-download"></i> Buka File
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-3 text-muted">Data surat tidak ditemukan.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Form Tindak Lanjut Utama --}}
            @if($disposisi->ke_user_id == auth()->id() && in_array($disposisi->status, ['pending', 'dibaca']))
            <div class="card shadow-sm border-0 border-top border-primary border-4 mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Tindak Lanjut Disposisi</h6>
                    
                    <form action="{{ route('disposisi.process', $disposisi->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Catatan Respon</label>
                            {{-- Diubah menjadi 'catatan_respon' agar tidak bentrok --}}
                            <textarea name="catatan_respon" class="form-control" rows="3" placeholder="Tuliskan tindakan yang dilakukan..." required></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="action" value="approve" class="btn btn-success">
                                <i class="bi bi-check-lg"></i> Tandai Selesai
                            </button>
                            
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalForward">
                                <i class="bi bi-share"></i> Teruskan ke User Lain
                            </button>

                            <hr>
                            
                            <button type="submit" name="action" value="reject" class="btn btn-outline-danger btn-sm" 
                                    onclick="return confirm('Tolak disposisi ini?')">
                                <i class="bi bi-x-circle"></i> Tolak / Kembalikan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Balas Langsung ke Pengirim --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-reply-fill"></i> Balas ke Pengirim</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('disposisi.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="letter_id" value="{{ $disposisi->letter_id }}">
                        <input type="hidden" name="ke_user_id" value="{{ $disposisi->dari_user_id }}">
                        <input type="hidden" name="parent_id" value="{{ $disposisi->id }}">
                        
                        <div class="mb-2">
                            <textarea name="instruksi" class="form-control form-control-sm" rows="3" 
                                      placeholder="Tulis balasan untuk {{ $disposisi->dari->nama_lengkap ?? 'Pengirim' }}..." required></textarea>
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

{{-- Modal Teruskan (Form Terpisah) --}}
<div class="modal fade" id="modalForward" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('disposisi.process', $disposisi->id) }}" method="POST">
            @csrf
            {{-- Input hidden 'action' sangat penting untuk logika Controller --}}
            <input type="hidden" name="action" value="forward">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Teruskan Disposisi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih User Tujuan</label>
                        <select name="ke_user_id" class="form-select" required>
                            <option value="">-- Cari User --</option>
                            @foreach(\App\Models\User::where('status','aktif')->where('id','!=',auth()->id())->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->nama_lengkap }} ({{ $user->jabatan }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Instruksi Tambahan</label>
                        {{-- Menggunakan 'instruksi_forward' agar unik --}}
                        <textarea name="instruksi_forward" class="form-control" rows="3" placeholder="Contoh: Tolong segera ditindak lanjuti." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses & Kirim</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto hide alert
        setTimeout(() => {
            document.querySelectorAll('.alert-dismissible').forEach(el => {
                const alert = new bootstrap.Alert(el);
                alert.close();
            });
        }, 5000);
    });
</script>
@endpush