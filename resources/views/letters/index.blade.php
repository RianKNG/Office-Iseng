@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-file-earmark-text text-primary"></i> Daftar Surat</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active">Surat</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('letters.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Buat Surat Baru
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

    <!-- 🔍 Live Filter (No Form Submit) -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cari Surat</label>
                    <input type="text" id="searchInput" class="form-control" 
                           value="{{ request('search') }}" placeholder="Nomor atau perihal...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jenis</label>
                    <select id="jenisSelect" class="form-select">
                        <option value="">Semua</option>
                        <option value="masuk" {{ request('jenis')=='masuk'?'selected':'' }}>Surat Masuk</option>
                        <option value="keluar" {{ request('jenis')=='keluar'?'selected':'' }}>Surat Keluar</option>
                        <option value="nota" {{ request('jenis')=='nota'?'selected':'' }}>Nota Dinas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="statusSelect" class="form-select">
                        <option value="">Semua</option>
                        <option value="draft" {{ request('status')=='draft'?'selected':'' }}>Draft</option>
                        <option value="menunggu_verifikasi" {{ request('status')=='menunggu_verifikasi'?'selected':'' }}>Menunggu Verifikasi</option>
                        <option value="disetujui" {{ request('status')=='disetujui'?'selected':'' }}>Selesai</option>
                        <option value="ditolak" {{ request('status')=='ditolak'?'selected':'' }}>Ditolak</option>
                        <option value="diproses" {{ request('status')=='diproses'?'selected':'' }}>Diproses</option>
                        <option value="arsip" {{ request('status')=='arsip'?'selected':'' }}>Arsip</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" id="fromDateInput" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="resetFilter" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 Statistics Cards -->
    <div class="row g-3 mb-4" id="statsContainer">
        <div class="col-md-3">
            <div class="card border-start border-4 border-primary h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Surat</h6>
                        <h3 class="mb-0" id="statTotal">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-files text-primary" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-warning h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Menunggu Verifikasi</h6>
                        <h3 class="mb-0" id="statWaiting">{{ $stats['waiting'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-clock-history text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-success h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Selesai</h6>
                        <h3 class="mb-0" id="statApproved">{{ $stats['approved'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-check-circle text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-danger h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Ditolak</h6>
                        <h3 class="mb-0" id="statRejected">{{ $stats['rejected'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-x-circle text-danger" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 📋 Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Daftar Surat</h5>
            <span class="badge bg-primary" id="countBadge">{{ $letters->count() }} Surat</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Nomor Surat</th>
                            <th width="10%">Jenis</th>
                            <th width="20%">Perihal</th>
                            <th width="10%">Tanggal</th>
                            <th width="12%">Penerima</th>
                            <th width="13%">Status</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @include('letters._table_rows')
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3" id="paginationContainer">
            @if($letters->hasPages())
                {{ $letters->withQueryString()->links() }}
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteForm" method="POST" action="">
                @csrf @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus surat ini?</p>
                    <p class="text-danger mb-0"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput   = document.getElementById('searchInput');
    const jenisSelect   = document.getElementById('jenisSelect');
    const statusSelect  = document.getElementById('statusSelect');
    const fromDateInput = document.getElementById('fromDateInput');
    const resetBtn      = document.getElementById('resetFilter');
    const tableBody     = document.getElementById('tableBody');
    const pagination    = document.getElementById('paginationContainer');
    const countBadge    = document.getElementById('countBadge');
    
    // Stats elements
    const statTotal     = document.getElementById('statTotal');
    const statWaiting   = document.getElementById('statWaiting');
    const statApproved  = document.getElementById('statApproved');
    const statRejected  = document.getElementById('statRejected');

    let debounceTimer;

    function fetchData(page = 1) {
        const params = new URLSearchParams({
            search: searchInput.value,
            jenis: jenisSelect.value,
            status: statusSelect.value,
            from_date: fromDateInput.value,
            page: page
        });

        fetch('{{ route("letters.index") }}?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            // Update UI
            tableBody.innerHTML = data.tableHtml;
            pagination.innerHTML = data.pagination;
            countBadge.textContent = data.count + ' Surat';
            
            // Update stats
            if (data.stats) {
                statTotal.textContent    = data.stats.total;
                statWaiting.textContent  = data.stats.waiting;
                statApproved.textContent = data.stats.approved;
                statRejected.textContent = data.stats.rejected;
            }

            // Re-bind delete buttons
            var deleteBtns = document.querySelectorAll('.btn-delete-trigger');
            for (var i = 0; i < deleteBtns.length; i++) {
                deleteBtns[i].addEventListener('click', (function(btn) {
                    return function() { confirmDelete(btn.dataset.id); };
                })(deleteBtns[i]));
            }
        })
        .catch(function(err) { console.error('Fetch error:', err); });
    }

    // Debounce untuk search
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { fetchData(1); }, 400);
    });

    // Fetch saat dropdown/tanggal berubah
    var filterElements = [jenisSelect, statusSelect, fromDateInput];
    for (var i = 0; i < filterElements.length; i++) {
        filterElements[i].addEventListener('change', (function(el) {
            return function() { fetchData(1); };
        })(filterElements[i]));
    }

    // Reset filter
    resetBtn.addEventListener('click', function() {
        searchInput.value = '';
        jenisSelect.value = '';
        statusSelect.value = '';
        fromDateInput.value = '';
        fetchData(1);
    });

    // Pagination click handler (AJAX)
    document.addEventListener('click', function(e) {
        var link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            var url = new URL(link.href);
            var page = url.searchParams.get('page') || 1;
            fetchData(page);
            window.scrollTo({ 
                top: document.querySelector('.card.shadow-sm').offsetTop - 100, 
                behavior: 'smooth' 
            });
        }
    });

    // Expose confirmDelete to window
    window.confirmDelete = function(id) {
        var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        document.getElementById('deleteForm').action = '/letters/' + id;
        modal.show();
    };
});
</script>
@endpush