@forelse($letters as $index => $letter)
<tr>
    <!-- No -->
    <td>{{ $letters->firstItem() + $index }}</td>
    
    <!-- Nomor Surat -->
    <td>
        <a href="{{ route('letters.show', $letter->id) }}" class="fw-bold text-decoration-none">
            {{ $letter->nomor_surat }}
        </a>
        @if($letter->file_path)
            <br><i class="bi bi-paperclip text-muted small"></i>
        @endif
    </td>
    
    <!-- Jenis -->
    <td>
        @php
            $jenisBadge = ['masuk'=>'info','keluar'=>'success','nota'=>'warning'];
            $jenisLabel = ['masuk'=>'Masuk','keluar'=>'Keluar','nota'=>'Nota'];
        @endphp
        <span class="badge bg-{{ $jenisBadge[$letter->jenis] ?? 'secondary' }}">
            {{ $jenisLabel[$letter->jenis] ?? ucfirst($letter->jenis) }}
        </span>
    </td>
    
    <!-- Perihal -->
    <td>{{ Str::limit($letter->perihal ?? '-', 40) }}</td>
    
    <!-- Tanggal -->
    <td>{{ $letter->tanggal ? $letter->tanggal->format('d M Y') : '-' }}</td>
    
    <!-- ✅ KOLOM BARU: Struktur/Unit Pembuat -->
    <td>
        @if($letter->creator)
            <small class="d-block">
                <span class="badge bg-secondary" style="font-size: 0.75em;">
                    {{ $letter->creator->getStrukturLabel() }}
                </span>
            </small>
            <small class="text-muted">
                {{ ucfirst($letter->creator->unit_kerja ?? 'umum') }}
            </small>
        @else
            <span class="text-muted small">-</span>
        @endif
    </td>
    
    <!-- Status -->
    <td>
        @php
            $statusBadge = [
                'draft' => 'secondary',
                'menunggu_verifikasi' => 'warning',
                'disetujui' => 'success',
                'diproses' => 'info',
                'ditolak' => 'danger',
                'arsip' => 'dark'
            ];
            $statusLabel = [
                'draft' => 'Draft',
                'menunggu_verifikasi' => 'Menunggu',
                'disetujui' => 'Selesai',  // ✅ Ubah dari 'Disetujui' ke 'Selesai'
                'diproses' => 'Diproses',
                'ditolak' => 'Ditolak',
                'arsip' => 'Arsip'
            ];
        @endphp
        <span class="badge bg-{{ $statusBadge[$letter->status] ?? 'secondary' }}">
            {{ $statusLabel[$letter->status] ?? ucfirst(str_replace('_', ' ', $letter->status)) }}
        </span>
    </td>
    
    <!-- Dibuat Oleh (dengan Level Label) -->
    <td>
        @if($letter->creator)
            <small>
                <strong>{{ $letter->creator->nama_lengkap }}</strong><br>
                <span class="text-muted" style="font-size: 0.85em;">
                    {{ $letter->creator->getLevelLabel() }}
                </span>
            </small>
        @else
            <span class="text-muted small">-</span>
        @endif
    </td>
    
    <!-- Aksi -->
    <td class="text-center">
        <div class="btn-group" role="group">
            <a href="{{ route('letters.show', $letter->id) }}" 
               class="btn btn-sm btn-outline-primary" 
               title="Lihat Detail">
                <i class="bi bi-eye">Lihat</i>
            </a>
            
            @if($letter->created_by == auth()->id() && in_array($letter->status, ['draft', 'menunggu_verifikasi']))
                <a href="{{ route('letters.edit', $letter->id) }}" 
                   class="btn btn-sm btn-outline-warning" 
                   title="Edit">
                    <i class="bi bi-pencil">Edit</i>
                </a>
                <button type="button" 
                        class="btn btn-sm btn-outline-danger btn-delete-trigger" 
                        data-id="{{ $letter->id }}" 
                        title="Hapus">
                    <i class="bi bi-trash">Hapus</i>
                </button>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-3 mb-0">Tidak ada data sesuai filter</p>
        <small class="text-muted">Coba ubah filter atau buat surat baru</small>
    </td>
</tr>
@endforelse