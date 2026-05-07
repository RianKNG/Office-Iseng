@extends('layouts.app')

@section('content')
{{-- <div class="container py-4"> --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📥 Inbox Disposisi</h4>
        <span class="badge bg-success">{{ $disposisi->total() }} Disposisi</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($disposisi->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Tanggal</th>
                                <th>Dari</th>
                                <th>Nomor Surat</th>
                                <th>Perihal</th>
                                <th>Instruksi</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($disposisi as $index => $disp)
                            <tr class="{{ $disp->status == 'pending' ? 'table-warning' : '' }}">
                                <td>{{ $disposisi->firstItem() + $index }}</td>
                                <td>{{ $disp->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <strong>{{ $disp->dari->nama_lengkap }}</strong><br>
                                    <small class="text-muted">{{ $disp->dari->jabatan }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('disposisi.show', $disp->id) }}" class="fw-bold">
                                        {{ $disp->letter->nomor_surat }}
                                    </a>
                                </td>
                                <td>{{ Str::limit($disp->letter->perihal, 30) }}</td>
                                <td><small>{{ Str::limit($disp->instruksi, 40) }}</small></td>
                                <td>
                                    @php
                                        $badge = [
                                            'biasa' => 'secondary',
                                            'penting' => 'warning',
                                            'segera' => 'danger',
                                            'rahasia' => 'dark'
                                        ][$disp->prioritas];
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">
                                        {{ ucfirst($disp->prioritas) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusBadge = [
                                            'pending' => 'warning',
                                            'dibaca' => 'info',
                                            'diproses' => 'primary',
                                            'diteruskan' => 'success',
                                            'selesai' => 'secondary'
                                        ][$disp->status];
                                    @endphp
                                    <span class="badge bg-{{ $statusBadge }}">
                                        {{ ucfirst($disp->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($disp->deadline)
                                        @if($disp->deadline < now() && $disp->status != 'selesai')
                                            <span class="text-danger fw-bold">⚠️ {{ $disp->deadline->format('d M Y') }}</span>
                                        @else
                                            <span class="text-muted">{{ $disp->deadline->format('d M Y') }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('disposisi.show', $disp->id) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="Lihat & Proses">
                                        👁️
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $disposisi->links() }}
            </div>
        </div>
    @else
        <div class="alert alert-info text-center">
            📭 Tidak ada disposisi masuk.
        </div>
    @endif
{{-- </div> --}}
@endsection