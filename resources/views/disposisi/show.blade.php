@extends('layouts.app')
@section('title', 'Detail Disposisi')
@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-inbox-fill text-primary"></i> Detail Disposisi</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('disposisi.inbox') }}">Inbox</a></li>
                    <li class="breadcrumb-item active">Detail #{{ $disposisi->id }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('disposisi.inbox') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <!-- Alert Status -->
    @if($disposisi->status == 'pending')
        <div class="alert alert-warning d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill me-2"></i><div><strong>Disposisi Baru!</strong> Disposisi ini menunggu tindakan Anda.</div></div>
    @elseif($disposisi->status == 'dibaca')
        <div class="alert alert-info d-flex align-items-center"><i class="bi bi-info-circle-fill me-2"></i><div>Disposisi ini sudah Anda baca. Silakan segera diproses.</div></div>
    @endif

    <div class="row g-4">
        <!-- Kolom Kiri: Informasi -->
        <div class="col-lg-8">
            <!-- Card: Informasi Disposisi -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-envelope-paper"></i> Informasi Disposisi</h5>
                    <span class="badge bg-light text-dark">{{ ucfirst($disposisi->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Dari</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">{{ substr($disposisi->dari->nama_lengkap ?? 'X', 0, 1) }}</div>
                                <div><strong>{{ $disposisi->dari->nama_lengkap ?? '-' }}</strong><br><small class="text-muted">{{ $disposisi->dari->jabatan ?? '-' }} @if($disposisi->dari) • {{ $disposisi->dari->getLevelLabel() }} • {{ $disposisi->dari->getStrukturLabel() }} @endif</small></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Kepada</label>
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">{{ substr($disposisi->ke->nama_lengkap ?? 'X', 0, 1) }}</div>
                                <div><strong>{{ $disposisi->ke->nama_lengkap ?? '-' }}</strong><br><small class="text-muted">{{ $disposisi->ke->jabatan ?? '-' }} @if($disposisi->ke) • {{ $disposisi->ke->getLevelLabel() }} • {{ $disposisi->ke->getStrukturLabel() }} @endif</small></div>
                            </div>
                        </div>
                        <div class="col-md-4"><label class="text-muted small mb-1">Tanggal</label><div class="fw-bold">{{ $disposisi->created_at ? $disposisi->created_at->format('d M Y') : '-' }}</div><small class="text-muted">{{ $disposisi->created_at ? $disposisi->created_at->format('H:i') : '-' }} WIB</small></div>
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Prioritas</label>
                            @php $pBadge = ['biasa'=>'secondary','penting'=>'warning','segera'=>'danger','rahasia'=>'dark']; $pIcon = ['biasa'=>'circle','penting'=>'exclamation-triangle','segera'=>'lightning-fill','rahasia'=>'lock-fill']; @endphp
                            <span class="badge bg-{{ $pBadge[$disposisi->prioritas] ?? 'secondary' }}"><i class="bi bi-{{ $pIcon[$disposisi->prioritas] ?? 'circle' }}"></i> {{ ucfirst($disposisi->prioritas) }}</span>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Deadline</label>
                            @if($disposisi->deadline)
                                @if($disposisi->deadline < now() && $disposisi->status != 'selesai') <span class="text-danger fw-bold"><i class="bi bi-exclamation-octagon"></i> {{ $disposisi->deadline->format('d M Y') }} (Terlambat!)</span>
                                @else <span class="text-success"><i class="bi bi-calendar-check"></i> {{ $disposisi->deadline->format('d M Y') }}</span> @endif
                            @else <span class="text-muted">-</span> @endif
                        </div>
                        <div class="col-12"><label class="text-muted small mb-1">Instruksi</label><div class="alert alert-light border mb-0"><p class="mb-0 fst-italic">"{{ $disposisi->instruksi ?? '-' }}"</p></div></div>
                        <div class="col-12"><label class="text-muted small mb-1">Tipe Alur</label><span class="badge {{ $disposisi->getTipeBadgeClass() }}">{!! $disposisi->getStrukturLabel() !!} @if($disposisi->isCrossStructure())<i class="bi bi-arrow-left-right ms-1"></i>@endif</span></div>
                    </div>
                </div>
            </div>

            <!-- Card: Informasi Surat -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Informasi Surat</h5>
                    <a href="{{ route('letters.show', $disposisi->letter->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> Lihat Surat</a>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><th width="30%">Nomor Surat</th><td><strong>{{ $disposisi->letter->nomor_surat ?? '-' }}</strong></td></tr>
                        <tr><th>Tanggal</th><td>{{ $disposisi->letter->tanggal ? $disposisi->letter->tanggal->format('d F Y') : '-' }}</td></tr>
                        <tr><th>Perihal</th><td>{{ $disposisi->letter->perihal ?? '-' }}</td></tr>
                        <tr><th>Jenis</th><td>@php $jBadge = ['masuk'=>'info','keluar'=>'success','nota'=>'warning']; @endphp <span class="badge bg-{{ $jBadge[$disposisi->letter->jenis] ?? 'secondary' }}">{{ ucfirst($disposisi->letter->jenis ?? '-') }}</span></td></tr>
                        <tr><th>Template</th><td>{{ $disposisi->letter->template ? $disposisi->letter->template->nama_template : '-' }}</td></tr>
                        <tr><th>Status Surat</th><td>@php $sBadge = ['draft'=>'secondary','menunggu_verifikasi'=>'warning','disetujui'=>'success','ditolak'=>'danger','diproses'=>'info','arsip'=>'dark']; $sLabel = ['draft'=>'Draft','menunggu_verifikasi'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak','diproses'=>'Diproses','arsip'=>'Arsip']; @endphp <span class="badge bg-{{ $sBadge[$disposisi->letter->status] ?? 'secondary' }}">{{ $sLabel[$disposisi->letter->status] ?? ucfirst(str_replace('_',' ',$disposisi->letter->status)) }}</span></td></tr>
                        <tr><th>Dibuat Oleh</th><td>@if($disposisi->letter->creator) {{ $disposisi->letter->creator->nama_lengkap }} <small class="text-muted d-block">{{ $disposisi->letter->creator->getLevelLabel() }} - {{ $disposisi->letter->creator->getStrukturLabel() }}</small> @else - @endif</td></tr>
                    </table>
                    @if($disposisi->letter->values && $disposisi->letter->values->count() > 0)
                        <hr class="my-4"><h6 class="fw-bold mb-3">📋 Detail Isi Surat</h6>
                        <div class="table-responsive"><table class="table table-sm table-bordered"><tbody>
                            @foreach($disposisi->letter->values as $val) <tr><th width="40%" class="bg-light">{{ $val->field ? $val->field->nama_field : 'Field Tidak Ditemukan' }}</th><td>{{ nl2br(e($val->nilai ?? '-')) }}</td></tr> @endforeach
                        </tbody></table></div>
                    @endif
                    @if($disposisi->letter->file_path)
                        <hr class="my-4"><div class="alert alert-info d-flex align-items-center"><i class="bi bi-paperclip me-2 fs-5"></i><div class="flex-grow-1"><strong>Lampiran tersedia</strong><br><small>Klik tombol di bawah untuk mengunduh</small></div><a href="{{ asset('storage/' . $disposisi->letter->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank"><i class="bi bi-download"></i> Download</a></div>
                    @endif
                </div>
            </div>
            @if($disposisi->parent_id && $disposisi->parent)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-diagram-3"></i> Disposisi Induk</h6></div>
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="flex-grow-1">
                                <p class="mb-1"><strong>Dari:</strong> {{ $disposisi->parent->dari->nama_lengkap ?? '-' }} @if($disposisi->parent->dari)<small class="text-muted">({{ $disposisi->parent->dari->getStrukturLabel() }})</small>@endif</p>
                                <p class="mb-1"><strong>Kepada:</strong> {{ $disposisi->parent->ke->nama_lengkap ?? '-' }} @if($disposisi->parent->ke)<small class="text-muted">({{ $disposisi->parent->ke->getStrukturLabel() }})</small>@endif</p>
                                <p class="mb-0"><strong>Instruksi:</strong> <em class="text-muted">"{{ Str::limit($disposisi->parent->instruksi ?? '-', 50) }}"</em></p>
                            </div>
                            <a href="{{ route('disposisi.show', $disposisi->parent->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-up-right"></i></a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Form Aksi -->
        <div class="col-lg-4">
            @if(in_array($disposisi->status, ['pending', 'dibaca']))
                <div class="card shadow-sm border-primary mb-4">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-gear-fill"></i> Proses Disposisi</h5></div>
                    <div class="card-body">
                        
                        {{-- ✅ FORM APPROVE (ID Unik: formApprove) --}}
                        <form id="formApprove" action="{{ route('disposisi.process', $disposisi->id) }}" method="POST" class="mb-3">
                            @csrf
                            <input type="hidden" name="action" id="actionApprove" value="approve">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Catatan Tindak Lanjut</label>
                                <textarea name="catatan_respon" class="form-control @error('catatan_respon') is-invalid @enderror" rows="3" placeholder="Tambahkan catatan...">{{ old('catatan_respon') }}</textarea>
                                @error('catatan_respon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @php $userLevel = auth()->user()->level; $isKasubagKasie = in_array($userLevel, ['kasubag', 'kasie', 'kanit', 'staff']); $isLeader = in_array($userLevel, ['kabag', 'kacab', 'dirut', 'admin']); @endphp
                            
                            {{-- Tombol untuk Kasie/Kasubag --}}
                            @if($isKasubagKasie && in_array($disposisi->status, ['pending', 'dibaca', 'diproses']))
                                <div class="d-grid gap-2 mb-2">
                                    <button type="button" onclick="submitForm('formApprove', 'proses')" class="btn btn-primary"><i class="bi bi-gear"></i> Tandai Diproses</button>
                                    <button type="button" onclick="submitForm('formApprove', 'selesai')" class="btn btn-success"><i class="bi bi-check-circle"></i> Selesai</button>
                                </div>
                            @endif
                            {{-- Tombol untuk Leader --}}
                            @if($isLeader && in_array($disposisi->status, ['pending', 'dibaca']))
                                <button type="button" onclick="submitForm('formApprove', 'approve')" class="btn btn-success w-100 mb-2"><i class="bi bi-check-circle"></i> Setujui & Teruskan</button>
                            @endif
                            <small class="text-muted d-block text-center">@if($isKasubagKasie) Klik "Selesai" untuk mengakhiri alur @else Akan diteruskan ke level berikutnya @endif</small>
                        </form>

                        <hr class="my-3">

                        {{-- ✅ FORM REJECT (ID Unik: formReject) --}}
                        <form id="formReject" action="{{ route('disposisi.process', $disposisi->id) }}" method="POST" class="mb-3">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-danger">Alasan Penolakan</label>
                                <textarea name="instruksi" class="form-control" rows="2" placeholder="Alasan surat ditolak..." required></textarea>
                            </div>
                            <button type="button" onclick="if(confirm('⚠️ Yakin ingin menolak?')) document.getElementById('formReject').submit();" class="btn btn-danger w-100"><i class="bi bi-x-circle"></i> Tolak Surat</button>
                        </form>

                        <hr class="my-3">

                        {{-- ✅ TOMBOL FORWARD (Modal Trigger) --}}
                        {{-- <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#modalForward"><i class="bi bi-share"></i> Teruskan ke User Lain</button>
                        <small class="text-muted d-block text-center mt-1">Teruskan ke rekan kerja</small> --}}
                        {{-- ✅ TOMBOL FORWARD (Bootstrap 5) --}}
{{-- ✅ TOMBOL FORWARD (Bootstrap 3 / 4) --}}
<button type="button" class="btn btn-outline-primary w-100" data-toggle="modal" data-target="#modalForward">
    <i class="bi bi-share"></i> Teruskan ke User Lain
</button>
<small class="text-muted d-block text-center mt-1">Teruskan ke rekan kerja</small>
                    </div>
                </div>
            @else
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light"><h6 class="mb-0">Status Disposisi</h6></div>
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            @php $icons = ['diproses'=>['icon'=>'check-circle-fill','color'=>'success','label'=>'Sudah Diproses'], 'diteruskan'=>['icon'=>'share-fill','color'=>'info','label'=>'Diteruskan'], 'selesai'=>['icon'=>'archive-fill','color'=>'secondary','label'=>'Selesai'], 'dikembalikan'=>['icon'=>'arrow-return-left','color'=>'warning','label'=>'Dikembalikan']]; $info = $icons[$disposisi->status] ?? ['icon'=>'clock','color'=>'muted','label'=>ucfirst($disposisi->status)]; @endphp
                            <i class="bi bi-{{ $info['icon'] }} text-{{ $info['color'] }}" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-{{ $info['color'] }}">{{ $info['label'] }}</h5>
                        </div>
                        <p class="text-muted mb-0">Diproses pada<br><strong>{{ $disposisi->updated_at ? $disposisi->updated_at->format('d M Y H:i') : '-' }}</strong></p>
                    </div>
                </div>
            @endif

            <!-- Card: Reply -->
            <div class="card shadow-sm">
                <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-chat-left-text"></i> Balas Disposisi</h6></div>
                <div class="card-body">
                    <form action="{{ route('disposisi.reply', $disposisi->id) }}" method="POST">
                        @csrf
                        <div class="mb-3"><label class="form-label small">Balasan untuk {{ $disposisi->dari->nama_lengkap ?? '-' }}</label><textarea name="instruksi" class="form-control" rows="3" placeholder="Tulis balasan..." required></textarea></div>
                        <div class="mb-3"><label class="form-label small">Prioritas</label><select name="prioritas" class="form-select"><option value="biasa">Biasa</option><option value="penting">Penting</option><option value="segera">Segera</option></select></div>
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-send"></i> Kirim Balasan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ MODAL FORWARD (Form yang BENAR) -->
<div class="modal fade" id="modalForward" tabindex="-1">
    <div class="modal-dialog">
        <form id="formForward" action="{{ route('disposisi.process', $disposisi->id) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="forward">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-share"></i> Teruskan Disposisi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info"><small>Teruskan disposisi ini ke user lain untuk ditindaklanjuti</small></div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Teruskan Kepada</label>
                        <select name="ke_user_id" class="form-control" required>
                            <option value="">-- Pilih User --</option>
                            @foreach($availableUsers as $user)
                                @if($user->id != auth()->id())
                                    <option value="{{ $user->id }}">{{ $user->nama_lengkap }} - {{ $user->jabatan }} ({{ $user->getLevelLabel() }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Instruksi Tambahan</label>
                        <textarea name="instruksi" class="form-control" rows="3" placeholder="Instruksi untuk user tujuan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" onclick="document.getElementById('formForward').submit();" class="btn btn-primary"><i class="bi bi-send"></i> Teruskan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ✅ Fungsi Submit Universal (Aman dari konflik ID)
function submitForm(formId, actionValue) {
    var form = document.getElementById(formId);
    if (!form) { console.error('Form not found: ' + formId); return; }
    
    // Update hidden input action di dalam form tersebut
    var actionInput = form.querySelector('input[name="action"]');
    if (actionInput) {
        actionInput.value = actionValue;
    }
    
    // Submit form yang spesifik
    form.submit();
}
</script>
@endpush