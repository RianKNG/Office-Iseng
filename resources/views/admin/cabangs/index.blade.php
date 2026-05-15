@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-building"></i> Manajemen Cabang</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Cabang</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.cabangs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Cabang
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm" 
                          value="{{ request('search') }}" placeholder="Nama atau kode...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Tipe</label>
                    <select name="tipe" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="pusat" {{ request('tipe')=='pusat'?'selected':'' }}>Pusat</option>
                        <option value="cabang" {{ request('tipe')=='cabang'?'selected':'' }}>Cabang</option>
                        <option value="unit" {{ request('tipe')=='unit'?'selected':'' }}>Unit</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('admin.cabangs') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Nama Cabang</th>
                            <th width="15%">Kode</th>
                            <th width="15%">Tipe</th>
                            <th width="30%">Alamat</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cabangs as $index => $cabang)
                        <tr>
                            <td>{{ $cabangs->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $cabang->nama_cabang }}</strong>
                                @if(strpos($cabang->nama_cabang, 'HAPUS:') === 0)
                                    <br><small class="text-danger"><i class="bi bi-trash"></i> Dinonaktifkan</small>
                                @endif
                            </td>
                            <td><code>{{ $cabang->kode }}</code></td>
                            <td>
                                @php
                                    $badgeTipe = ['pusat'=>'bg-primary','cabang'=>'bg-success','unit'=>'bg-info'];
                                @endphp
                                <span class="badge {{ $badgeTipe[$cabang->tipe] ?? 'bg-secondary' }}">
                                    {{ ucfirst($cabang->tipe) }}
                                </span>
                            </td>
                            <td><small class="text-muted">{{ Str::limit($cabang->alamat, 40) }}</small></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.cabangs.edit', $cabang->id) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.cabangs.destroy', $cabang->id) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('⚠️ Yakin ingin menghapus cabang ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($cabangs->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                Tidak ada data cabang
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $cabangs->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection