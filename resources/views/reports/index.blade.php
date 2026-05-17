@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📊 Laporan Surat Digital</h5>
                    <div>
                        <a href="{{ route('reports.export.pdf', request()->all()) }}" 
                           class="btn btn-light btn-sm me-2" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Cetak
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- 📈 Grafik Statistik (Chart.js) -->
                    <div class="row mb-4">
                        <div class="col-lg-6 col-md-12 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribusi Status Surat</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik Ringkas</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="barChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 📊 Statistik Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="card bg-primary text-white h-100 shadow-sm border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                                    <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                                    <small class="text-white-50">Total Surat</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="card bg-warning text-white h-100 shadow-sm border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <h2 class="mb-0 fw-bold">{{ $stats['menunggu_verifikasi'] }}</h2>
                                    <small class="text-white-50">Menunggu Verifikasi</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="card bg-info text-white h-100 shadow-sm border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-spinner fa-2x mb-2"></i>
                                    <h2 class="mb-0 fw-bold">{{ $stats['diproses'] }}</h2>
                                    <small class="text-white-50">Diproses</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="card bg-success text-white h-100 shadow-sm border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <h2 class="mb-0 fw-bold">{{ $stats['disetujui'] }}</h2>
                                    <small class="text-white-50">Disetujui</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="card bg-secondary text-white h-100 shadow-sm border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-flag-checkered fa-2x mb-2"></i>
                                    <h2 class="mb-0 fw-bold">{{ $stats['selesai'] }}</h2>
                                    <small class="text-white-50">Selesai</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="card bg-danger text-white h-100 shadow-sm border-0">
                                <div class="card-body text-center py-3">
                                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                                    <h2 class="mb-0 fw-bold">
                                        {{ $stats['total'] - ($stats['menunggu_verifikasi'] + $stats['diproses'] + $stats['disetujui'] + $stats['selesai']) }}
                                    </h2>
                                    <small class="text-white-50">Ditolak/Lainnya</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 🔍 Filter Form -->
                    <form method="GET" action="{{ route('reports.index') }}" class="mb-4">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fas fa-filter me-2"></i>Filter Pencarian</h6>
                                <div class="row g-3 align-items-end">
                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label fw-bold small">Tanggal Mulai</label>
                                        <input type="date" name="start_date" class="form-control form-control-sm" 
                                               value="{{ $startDate }}" required>
                                    </div>
                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label fw-bold small">Tanggal Akhir</label>
                                        <input type="date" name="end_date" class="form-control form-control-sm" 
                                               value="{{ $endDate }}" required>
                                    </div>
                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label fw-bold small">Status</label>
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                                            @foreach($statuses as $s)
                                                <option value="{{ $s }}" {{ $status == $s ? 'selected' : '' }}>
                                                    {{ ucfirst(str_replace('_', ' ', $s)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-md-6">
                                        <label class="form-label fw-bold small">Template</label>
                                        <select name="template" class="form-select form-select-sm">
                                            <option value="all" {{ $template == 'all' ? 'selected' : '' }}>Semua Template</option>
                                            @foreach($templates as $tpl)
                                                <option value="{{ $tpl->id }}" {{ $template == $tpl->id ? 'selected' : '' }}>
                                                    {{ $tpl->nama_template }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label fw-bold small">Pencarian</label>
                                        <input type="text" name="search" class="form-control form-control-sm" 
                                               placeholder="Cari nomor, perihal, pembuat..." 
                                               value="{{ request('search') }}">
                                    </div>
                                    <div class="col-lg-1 col-md-6 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm w-100" title="Cari">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm" title="Reset">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- 📋 Tabel Laporan -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-dark text-nowrap">
                                <tr>
                                    <th width="4%" class="text-center">No</th>
                                    <th width="14%">Nomor Surat</th>
                                    <th width="22%">Perihal</th>
                                    <th width="12%">Template</th>
                                    <th width="14%">Pembuat</th>
                                    <th width="10%" class="text-center">Status</th>
                                    <th width="10%" class="text-center">Tanggal</th>
                                    <th width="14%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($letters as $index => $letter)
                                <tr class="text-nowrap">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="fw-bold text-primary">{{ $letter->nomor_surat }}</span>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                              title="{{ $letter->perihal }}">
                                            {{ Str::limit($letter->perihal, 40) }}
                                        </span>
                                    </td>
                                    <td>{{ $letter->template->nama_template ?? '-' }}</td>
                                    <td>{{ $letter->creator->nama_lengkap ?? '-' }}</td>
                                    <td class="text-center">
                                        @php
                                            $badgeConfig = [
                                                'menunggu_verifikasi' => ['class' => 'warning', 'icon' => 'clock'],
                                                'diproses' => ['class' => 'info', 'icon' => 'spinner'],
                                                'disetujui' => ['class' => 'success', 'icon' => 'check'],
                                                'selesai' => ['class' => 'secondary', 'icon' => 'flag'],
                                                'ditolak' => ['class' => 'danger', 'icon' => 'times']
                                            ];
                                            $config = $badgeConfig[$letter->status] ?? ['class' => 'secondary', 'icon' => 'question'];
                                        @endphp
                                        <span class="badge bg-{{ $config['class'] }} px-3 py-2">
                                            <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $letter->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">
                                            {{ $letter->created_at->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('letters.show', $letter->id) }}" 
                                               class="btn btn-info" 
                                               target="_blank"
                                               title="Lihat Detail"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('letters.download', $letter->id) }}" 
                                               class="btn btn-success"
                                               title="Download PDF"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-inbox fa-4x text-muted mb-3 d-block"></i>
                                        <p class="text-muted mb-0">Tidak ada data surat yang ditemukan</p>
                                        <small class="text-muted">Coba ubah filter pencarian Anda</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{-- TAMPILAN PAGINATION MANUAL BOOTSTRAP --}}
{{-- Letakkan kode ini di bawah tag penutup tabel </table> --}}
<div class="d-flex justify-content-between align-items-center mt-3 px-2">
    <div class="text-muted small">
        Showing {{ $letters->firstItem() }} to {{ $letters->lastItem() }} of {{ $letters->total() }} results
    </div>

    <nav aria-label="Page navigation">
        <ul class="pagination pagination-sm m-0">
            
            {{-- Tombol Previous --}}
            @if ($letters->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&laquo; Previous</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $letters->previousPageUrl() }}">&laquo; Previous</a></li>
            @endif

            {{-- Angka Halaman --}}
            @for ($i = 1; $i <= $letters->lastPage(); $i++)
                <li class="page-item {{ $i == $letters->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $letters->url($i) }}">{{ $i }}</a>
                </li>
            @endfor

            {{-- Tombol Next --}}
            @if ($letters->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $letters->nextPageUrl() }}">Next &raquo;</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>
            @endif

        </ul>
    </nav>
</div>
                
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data dari Laravel
    const stats = {
        menunggu: {{ $stats['menunggu_verifikasi'] }},
        diproses: {{ $stats['diproses'] }},
        disetujui: {{ $stats['disetujui'] }},
        selesai: {{ $stats['selesai'] }},
        lainnya: {{ $stats['total'] - ($stats['menunggu_verifikasi'] + $stats['diproses'] + $stats['disetujui'] + $stats['selesai']) }}
    };

    // 🥧 Pie Chart - Distribusi Status
    const ctxPie = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Menunggu Verifikasi', 'Diproses', 'Disetujui', 'Selesai', 'Lainnya'],
            datasets: [{
                data: [stats.menunggu, stats.diproses, stats.disetujui, stats.selesai, stats.lainnya],
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#6c757d', '#dc3545'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 15 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // 📊 Bar Chart - Perbandingan
    const ctxBar = document.getElementById('barChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Menunggu', 'Diproses', 'Disetujui', 'Selesai'],
            datasets: [{
                label: 'Jumlah Surat',
                data: [stats.menunggu, stats.diproses, stats.disetujui, stats.selesai],
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#6c757d'],
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    // Tooltip Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection