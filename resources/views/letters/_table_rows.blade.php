@php $no = ($letters->currentPage() - 1) * $letters->perPage() + 1; @endphp

@foreach($letters as $letter)
<tr>
    <td>{{ $no++ }}</td>
    <td>
        <strong>{{ $letter->nomor_surat }}</strong><br>
        <small class="text-muted">{{ $letter->template->nama_template ?? '-' }}</small>
    </td>
    <td>
        @php
            $jenisBadge = [
                'masuk' => 'bg-info',
                'keluar' => 'bg-success',
                'nota' => 'bg-warning text-dark'
            ];
        @endphp
        <span class="badge {{ $jenisBadge[$letter->jenis] ?? 'bg-secondary' }}">
            {{ ucfirst($letter->jenis) }}
        </span>
    </td>
    <td>{{ Str::limit($letter->perihal, 30) }}</td>
    <td>{{ $letter->tanggal ? $letter->tanggal->format('d/m/Y') : '-' }}</td>
    
    {{-- ✅ KOLOM PENERIMA (baru) --}}
    <td>
        @if($letter->penerima)
            <div class="d-flex align-items-center">
                <i class="bi bi-person me-2 text-muted"></i>
                <div>
                    <div class="fw-bold">{{ $letter->penerima->nama_lengkap }}</div>
                    <small class="text-muted">{{ $letter->penerima->jabatan }}</small>
                </div>
            </div>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    
    <td>
        @php
            $statusBadge = [
                'draft' => 'bg-secondary',
                'menunggu_verifikasi' => 'bg-warning text-dark',
                'disetujui' => 'bg-success',
                'ditolak' => 'bg-danger',
                'diproses' => 'bg-primary',
                'arsip' => 'bg-info text-dark'
            ];
            $statusLabel = [
                'draft' => 'Draft',
                'menunggu_verifikasi' => 'Menunggu',
                'disetujui' => 'Selesai',
                'ditolak' => 'Ditolak',
                'diproses' => 'Diproses',
                'arsip' => 'Arsip'
            ];
        @endphp
        <span class="badge {{ $statusBadge[$letter->status] ?? 'bg-secondary' }}">
            {{ $statusLabel[$letter->status] ?? $letter->status }}
        </span>
    </td>
    <td class="text-center">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('letters.show', $letter->id) }}" class="btn btn-outline-primary" title="Lihat">
                <i class="bi bi-eye"></i>
            </a>
            @if($letter->status === 'draft' && $letter->created_by === auth()->id())
            <a href="{{ route('letters.edit', $letter->id) }}" class="btn btn-outline-warning" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            @endif
            @if($letter->created_by === auth()->id() && in_array($letter->status, ['draft', 'ditolak']))
            <button type="button" class="btn btn-outline-danger btn-delete-trigger" 
                    data-id="{{ $letter->id }}" title="Hapus">
                <i class="bi bi-trash"></i>
            </button>
            @endif
        </div>
    </td>
</tr>
@endforeach

@if($letters->isEmpty())
<tr>
    <td colspan="8" class="text-center py-4 text-muted">
        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
        Tidak ada surat ditemukan.
    </td>
</tr>
@endif