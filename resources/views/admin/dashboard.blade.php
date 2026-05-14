@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>👑 Admin Dashboard</h3>
        <div>
            <span class="badge bg-primary">{{ auth()->user()->nama_lengkap }}</span>
            <span class="badge bg-success ms-2">{{ auth()->user()->getLevelLabel() }}</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1"></i>
                    <h5 class="mt-2">Total User</h5>
                    <h3>{{ $stats['total_users'] }}</h3>
                    <small>{{ $stats['users_aktif'] }} Aktif</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-file-earmark-text fs-1"></i>
                    <h5 class="mt-2">Total Surat</h5>
                    <h3>{{ $stats['total_surat'] }}</h3>
                    <small>{{ $stats['surat_bulan_ini'] }} Bulan Ini</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-left-right fs-1"></i>
                    <h5 class="mt-2">Total Disposisi</h5>
                    <h3>{{ $stats['total_disposisi'] }}</h3>
                    <small>{{ $stats['disposisi_pending'] }} Pending</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">⚡ Akses Cepat</h6>
        </div>
        <div class="card-body">
    <div class="d-flex gap-2 flex-wrap">
        {{-- ✅ Kelola User --}}
        <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
            <i class="bi bi-people"></i> Kelola User
        </a>
        
        {{-- ✅ Tambah User (HAPUS nested <a>) --}}
        <a href="{{ route('admin.users.create') }}" class="btn btn-success">
            <i class="bi bi-person-plus"></i> Tambah User
        </a>
        
        {{-- ✅ Lihat Semua Surat --}}
        <a href="{{ route('admin.letters') }}" class="btn btn-info text-white">
            <i class="bi bi-files"></i> Lihat Semua Surat
        </a>
        
        {{-- ✅ Lihat Disposisi --}}
        <a href="{{ route('admin.disposisi') }}" class="btn btn-warning">
            <i class="bi bi-inbox"></i> Lihat Disposisi
        </a>
    </div>
</div>
    <!-- Recent Activities -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between">
            <h6 class="mb-0">📋 Aktivitas Terbaru</h6>
            <a href="{{ route('admin.disposisi') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanghhhhhgal</th>
                            <th>Nomor Surat</th>
                            <th>Dari</th>
                            <th>Ke</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivities as $activity)
                        <tr>
                            <td>{{ $activity->created_at->format('d/m H:i') }}</td>
                            <td>{{ $activity->letter->nomor_surat ?? '-' }}</td>
                            <td>{{ $activity->dari->nama_lengkap ?? '-' }}</td>
                            <td>{{ $activity->ke->nama_lengkap ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $activity->status == 'pending' ? 'warning' : 'success' }}">
                                    {{ ucfirst($activity->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Belum ada aktivitas
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection