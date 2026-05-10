@forelse($letters as $index => $letter)
<tr>
    
    <td>{{ $letters->firstItem() + $index }}</td>
    <td>
        <a href="{{ route('letters.show', $letter->id) }}" class="fw-bold text-decoration-none">{{ $letter->nomor_surat }}</a>
        @if($letter->file_path)<br><i class="bi bi-paperclip text-muted small"></i>@endif
    </td>
    <td>
        @php
            $jenisBadge = ['masuk'=>'info','keluar'=>'success','nota'=>'warning'][$letter->jenis];
            $jenisLabel = ['masuk'=>'Surat Masuk','keluar'=>'Surat Keluar','nota'=>'Nota Dinas'][$letter->jenis];
        @endphp
        <span class="badge bg-{{ $jenisBadge }}">{{ $jenisLabel }}</span>
    </td>
    <td>{{ Str::limit($letter->perihal, 40) }}</td>
    <td>{{ $letter->tanggal ? $letter->tanggal->format('d M Y') : '-' }}</td>
    <td>
        @php
            $statusBadge = ['draft'=>'warning','menunggu_verifikasi'=>'warning','disetujui'=>'success','diproses'=>'success','ditolak'=>'danger','arsip'=>'dark'][$letter->status];
            $statusLabel = ['draft'=>'Draft','menunggu_verifikasi'=>'Menunggu Verifikasi','disetujui'=>'Disetujui','diproses'=>'Diproses','ditolak'=>'Ditolak','arsip'=>'Arsip'][$letter->status];
        @endphp
        <span class="badge bg-{{ $statusBadge }}">{{ $statusLabel }}</span>
    </td>
    <td>
        <small><strong>{{ $letter->creator->nama_lengkap ?? '-' }}</strong><br><span class="text-muted">{{ $letter->created_at->diffForHumans() }}</span></small>
    </td>
    <td class="text-center">
        <div class="btn-group" role="group">
            <a href="{{ route('letters.show', $letter->id) }}" class="btn btn-sm btn-outline-primary" title="Lihat"><i class="bi bi-eye">Lihat</i></a>
            @if($letter->created_by == auth()->id() && $letter->status == 'draft')
            <a href="{{ route('letters.edit', $letter->id) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil">Edit</i></a>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-trigger" data-id="{{ $letter->id }}" title="Hapus"><i class="bi bi-trash">Hapus</i></button>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-3 mb-0">Tidak ada data sesuai filter</p>
    </td>
</tr>
@endforelse