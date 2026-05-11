@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-inbox-fill text-primary"></i> Inbox Disposisi
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active">Disposisi Masuk</li>
                </ol>
            </nav>
        </div>
        <span class="badge bg-success fs-6">
            {{ $disposisi->total() }} Disposisi
        </span>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 🔍 Filter (Opsional: hanya untuk Kabag/Dirut) -->
    @if(auth()->user()->level_urutan >= 3)
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm" 
                           value="{{ request('search') }}" placeholder="Nomor surat / perihal...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Prioritas</label>
                    <select name="prioritas" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="biasa" {{ request('prioritas')=='biasa'?'selected':'' }}>Biasa</option>
                        <option value="penting" {{ request('prioritas')=='penting'?'selected':'' }}>Penting</option>
                        <option value="segera" {{ request('prioritas')=='segera'?'selected':'' }}>Segera</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                        <option value="dibaca" {{ request('status')=='dibaca'?'selected':'' }}>Dibaca</option>
                        <option value="diproses" {{ request('status')=='diproses'?'selected':'' }}>Diproses</option>
                        <option value="selesai" {{ request('status')=='selesai'?'selected':'' }}>Selesai</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Struktur</label>
                    <select name="struktur" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="pusat" {{ request('struktur')=='pusat'?'selected':'' }}>Pusat</option>
                        <option value="cabang" {{ request('struktur')=='cabang'?'selected':'' }}>Cabang</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-50">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('disposisi.inbox') }}" class="btn btn-outline-secondary btn-sm w-50">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- 📋 Table -->
    @if($disposisi->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Tanggal</th>
                                <th width="18%">Dari</th>
                                <th width="15%">Nomor Surat</th>
                                <th width="15%">Perihal</th>
                                <th width="12%">Instruksi</th>
                                <th width="8%">Prioritas</th>
                                <th width="8%">Status</th>
                                <th width="10%">Deadline</th>
                                <th width="7%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($disposisi as $index => $disp)
                            <tr class="{{ $disp->status == 'pending' ? 'table-warning' : '' }}">
                                <!-- No -->
                                <td>{{ $disposisi->firstItem() + $index }}</td>
                                
                                <!-- Tanggal -->
                                <td>
                                    <small class="d-block fw-bold">
                                        {{ $disp->created_at->format('d M Y') }}
                                    </small>
                                    <small class="text-muted">
                                        {{ $disp->created_at->format('H:i') }} WIB
                                    </small>
                                </td>
                                
                                <!-- Dari (dengan Struktur/Unit & Level Label) -->
                                <td>
                                    @if($disp->dari)
                                        <strong class="d-block">{{ $disp->dari->nama_lengkap }}</strong>
                                        <small class="text-muted d-block">
                                            {{ $disp->dari->jabatan ?? '-' }}
                                        </small>
                                        <!-- Badge Struktur & Level -->
                                        <div class="mt-1">
                                            <span class="badge bg-secondary" style="font-size: 0.7em;">
                                                {{ $disp->dari->getStrukturLabel() }}
                                            </span>
                                            <span class="badge bg-info" style="font-size: 0.7em;">
                                                {{ $disp->dari->getLevelLabel() }}
                                            </span>
                                        </div>
                                        <!-- Unit Kerja -->
                                        <small class="text-muted" style="font-size: 0.8em;">
                                            {{ ucfirst($disp->dari->unit_kerja ?? 'umum') }}
                                        </small>
                                    @else
                                        <span class="text-muted"></span>
                                    @endif
                                </td>
                                
                                <!-- Nomor Surat -->
                                <td>
                                    <a href="{{ route('disposisi.show', $disp->id) }}" 
                                       class="fw-bold text-decoration-none text-primary">
                                        {{ $disp->letter->nomor_surat ?? '-' }}
                                    </a>
                                    @if($disp->letter->file_path)
                                        <br><i class="bi bi-paperclip text-muted small"></i>
                                    @endif
                                </td>
                                
                                <!-- Perihal -->
                                <td>
                                    <small>{{ Str::limit($disp->letter->perihal ?? '-', 35) }}</small>
                                </td>
                                
                                <!-- Instruksi -->
                                <td>
                                    <small class="fst-italic text-muted">
                                        "{{ Str::limit($disp->instruksi ?? '-', 40) }}"
                                    </small>
                                </td>
                                
                                <!-- Prioritas -->
                                <td>
                                    @php
                                        $prioritasBadge = [
                                            'biasa' => 'secondary',
                                            'penting' => 'warning',
                                            'segera' => 'danger',
                                            'rahasia' => 'dark'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $prioritasBadge[$disp->prioritas] ?? 'secondary' }}">
                                        {{ ucfirst($disp->prioritas) }}
                                    </span>
                                </td>
                                
                                <!-- Status -->
                                <td>
                                    @php
                                        $statusBadge = [
                                            'pending' => 'warning',
                                            'dibaca' => 'info',
                                            'diproses' => 'primary',
                                            'diteruskan' => 'success',
                                            'selesai' => 'secondary',
                                            'dikembalikan' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusBadge[$disp->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $disp->status)) }}
                                    </span>
                                </td>
                                
                                <!-- Deadline -->
                                <td>
                                    @if($disp->deadline)
                                        @if($disp->deadline < now() && $disp->status != 'selesai')
                                            <span class="text-danger fw-bold small">
                                                <i class="bi bi-exclamation-octagon"></i> 
                                                {{ $disp->deadline->format('d M') }}
                                            </span>
                                        @else
                                            <span class="text-muted small">
                                                {{ $disp->deadline->format('d M') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                
                                <!-- Aksi -->
                                <td class="text-center">
                                    <a href="{{ route('disposisi.show', $disp->id) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="Lihat & Proses">
                                        <i class="bi bi-eye">Lihat</i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $disposisi->withQueryString()->links() }}
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">Tidak ada disposisi masuk</h5>
                <p class="text-muted mb-0">
                    @if(request()->hasAny(['search', 'prioritas', 'status', 'struktur']))
                        Coba reset filter untuk melihat semua disposisi
                    @else
                        Disposisi akan muncul ketika ada surat yang diteruskan ke Anda
                    @endif
                </p>
                @if(request()->hasAny(['search', 'prioritas', 'status', 'struktur']))
                    <a href="{{ route('disposisi.inbox') }}" class="btn btn-outline-primary mt-3">
                        <i class="bi bi-x-circle"></i> Reset Filter
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection