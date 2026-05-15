@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-person-badge"></i> Manajemen Jabatan</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Jabatan</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.jabatans.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Jabatan
        </a>
    </div>

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
                <div class="col-md-5">
                    <label class="form-label small">Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm" 
                          value="{{ request('search') }}" placeholder="Nama jabatan atau level...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Scope</label>
                    <select name="scope" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="pusat" {{ request('scope')=='pusat'?'selected':'' }}>Pusat</option>
                        <option value="cabang" {{ request('scope')=='cabang'?'selected':'' }}>Cabang</option>
                        <option value="unit" {{ request('scope')=='unit'?'selected':'' }}>Unit</option>
                        <option value="semua" {{ request('scope')=='semua'?'selected':'' }}>Semua</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('admin.jabatans') }}" class="btn btn-outline-secondary btn-sm w-100">
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
                            <th width="30%">Nama Jabatan</th>
                            <th width="15%">Level Key</th>
                            <th width="10%">Urutan</th>
                            <th width="15%">Scope</th>
                            <th width="10%">User</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jabatans as $index => $jabatan)
                        <tr>
                            <td>{{ $jabatans->firstItem() + $index }}</td>
                            <td><strong>{{ $jabatan->nama_jabatan }}</strong></td>
                            <td>
                                @php
                                    $badgeLevel = ['admin'=>'bg-dark','dirut'=>'bg-danger','kabag'=>'bg-primary',
                                                  'kacab'=>'bg-success','kanit'=>'bg-info','kasubag'=>'bg-warning',
                                                  'kasie'=>'bg-warning','staff'=>'bg-secondary'];
                                @endphp
                                <span class="badge {{ $badgeLevel[$jabatan->level_key] ?? 'bg-secondary' }}">
                                    {{ $jabatan->level_key }}
                                </span>
                            </td>
                            <td class="text-center">{{ $jabatan->urutan }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst($jabatan->scope) }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{ \App\Models\User::where('jabatan_id', $jabatan->id)->count() }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.jabatans.edit', $jabatan->id) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.jabatans.destroy', $jabatan->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('⚠️ Yakin ingin menghapus jabatan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($jabatans->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                Tidak ada data jabatan
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $jabatans->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection