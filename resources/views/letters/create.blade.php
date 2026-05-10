@extends('layouts.app')

@section('content')
{{-- @push('styles')
<style>
    .bg-gradient-success { background: linear-gradient(135deg, #28a745 0%, #218838 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); }
    .bg-gradient-info { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
    .card { transition: transform 0.2s, box-shadow 0.2s; }
    .card:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15)!important; }
    .form-select:focus, .form-control:focus { border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25); }
    .input-group-text { background-color: #f8f9fa; border-right: none; }
    .input-group .form-control, .input-group .form-select { border-left: none; }
    #preview-content { max-height: 70vh; overflow-y: auto; }
    .template-header { animation: slideDown 0.3s ease-out; }
    @keyframes slideDown { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:translateY(0); } }
    .spinner-border { width: 3rem; height: 3rem; }
    .alert { border-radius: 10px; }
    .btn { border-radius: 8px; padding: 10px 24px; font-weight: 600; transition: all 0.3s; }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
    .btn-success { background: linear-gradient(135deg, #28a745 0%, #218838 100%); border: none; }
    .btn-info { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); border: none; }
</style>
@endpush --}}

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📝 Buat Surat Baru</h5>
                    <a href="{{ route('letters.index') }}" class="btn btn-light btn-sm">← Kembali</a>
                </div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('letters.store') }}" id="letterForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- ⚠️ PENTING: Hidden input untuk ke_user_id -->
                        <input type="hidden" name="ke_user_id" id="ke_user_id_hidden" value="">

                        <!-- Pilih Template -->
                        <div class="mb-3">
                            <label for="template_id" class="form-label fw-bold">Pilih Template Surat</label>
                            <select id="template_id" name="template_id" class="form-select @error('template_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis Surat --</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}" 
                                            data-jenis="{{ $tpl->jenis }}"
                                            data-kode="{{ $tpl->kode_template }}">
                                        {{ $tpl->nama_template }} ({{ $tpl->kode_template }})
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Field Standar -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="nomor_surat" class="form-label fw-bold">Nomor Surat</label>
                                <input type="text" id="nomor_surat" name="nomor_surat" 
                                    class="form-control @error('nomor_surat') is-invalid @enderror" 
                                    value="{{ old('nomor_surat') }}" readonly placeholder="Auto-generate">
                                <small class="text-muted">Nomor akan digenerate otomatis</small>
                                @error('nomor_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal" class="form-label fw-bold">Tanggal Surat</label>
                                <input type="date" id="tanggal" name="tanggal" 
                                    class="form-control @error('tanggal') is-invalid @enderror" 
                                    value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="perihal" class="form-label fw-bold">Perihal</label>
                            <input type="text" id="perihal" name="perihal" 
                                class="form-control @error('perihal') is-invalid @enderror" 
                                value="{{ old('perihal') }}" required placeholder="Ringkasan isi surat">
                            @error('perihal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Dynamic Fields Container -->
                        <div id="dynamic-fields" class="mt-4 p-3 bg-light rounded border d-none">
                            <h6 class="fw-bold mb-3">📋 Detail Template</h6>
                            <div id="fields-content"></div>
                        </div>

                        <!-- File Upload (Opsional) -->
                        <div class="mb-3 mt-3">
                            <label for="file_path" class="form-label">Lampiran File (Opsional)</label>
                            <input type="file" id="file_path" name="file_path" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
                            <small class="text-muted">Maks. 10MB. Format: PDF, DOC, JPG, PNG</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                            <button type="button" id="btnPreview" class="btn btn-info me-md-2" disabled>👁️ Preview</button>
                            <button type="submit" id="btnSubmit" class="btn btn-success px-4" disabled>💾 Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">👁️ Preview Surat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="preview-content" class="p-4 border rounded bg-white"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Cetak</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('template_id');
    const dynamicFields = document.getElementById('dynamic-fields');
    const fieldsContent = document.getElementById('fields-content');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnPreview = document.getElementById('btnPreview');
    const nomorSuratInput = document.getElementById('nomor_surat');
    const keUserIdHidden = document.getElementById('ke_user_id_hidden');
    
    let currentFields = [];
    let usersData = @json($users ?? []);
    const currentUser = @json(auth()->user());

    const templateConfig = {
        1: { name: 'SM-UMUM', title: 'SURAT MASUK', icon: '📥', color: 'info' },
        2: { name: 'SK-RESMI', title: 'SURAT KELUAR', icon: '📤', color: 'success' },
        3: { name: 'ND-INT', title: 'NOTA DINAS', icon: '📝', color: 'warning' }
    };

    // Generate nomor surat
    async function generateNomorSurat(templateCode) {
        try {
            const response = await fetch(`/api/generate-nomor-surat?kode=${templateCode}`);
            const data = await response.json();
            if (data.nomor) nomorSuratInput.value = data.nomor;
        } catch (error) {
            const today = new Date();
            const bulan = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][today.getMonth()];
            nomorSuratInput.value = `001/${templateCode}/${bulan}/${today.getFullYear()}`;
        }
    }

    // ✅ FUNGSI SYNC: Update hidden input ke_user_id
    window.syncPenerima = function(userId) {
        if (keUserIdHidden) {
            keUserIdHidden.value = userId;
            console.log('✅ [SYNC] ke_user_id =', userId);
        }
        updatePreview();
    };

    function renderTemplateHeader(templateId) {
        const config = templateConfig[templateId];
        if (!config) return '';
        const gradientClass = config.color === 'success' ? 'bg-gradient-success' : 
                             config.color === 'warning' ? 'bg-gradient-warning' : 'bg-gradient-info';
        return `<div class="template-header mb-4 p-4 rounded-3 ${gradientClass} text-white shadow-sm">
            <div class="d-flex align-items-center">
                <div class="display-4 me-3">${config.icon}</div>
                <div><h4 class="mb-1 fw-bold">${config.title}</h4><p class="mb-0 opacity-75">${config.name}</p></div>
            </div></div>`;
    }

    templateSelect.addEventListener('change', async function() {
        const templateId = this.value;
        const templateCode = this.options[this.selectedIndex]?.dataset.kode || '';
        
        if (!templateId) {
            dynamicFields.classList.add('d-none');
            if (keUserIdHidden) keUserIdHidden.value = '';
            return;
        }

        await generateNomorSurat(templateCode);
        let html = renderTemplateHeader(templateId);
        html += '<div class="row g-3">';
        fieldsContent.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
        dynamicFields.classList.remove('d-none');

        try {
            const response = await fetch(`/api/template/${templateId}/fields`);
            const fields = await response.json();
            currentFields = fields || [];

            fields.forEach(field => {
                const req = field.is_required ? 'required' : '';
                const name = `fields[${field.id}]`;
                const fieldNameLower = field.nama_field.toLowerCase();
                
                html += `<div class="col-12">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <label class="form-label fw-bold">${field.nama_field} ${field.is_required ? '<span class="text-danger">*</span>' : ''}</label>`;

                // ✅ PERBAIKAN: Cek field penerima untuk SEMUA template
                const isPenerimaField = fieldNameLower === 'kepada' 
                                     || fieldNameLower === 'kepada_nd' 
                                     || fieldNameLower === 'penerima' 
                                     || fieldNameLower === 'disposisi' 
                                     || fieldNameLower === 'tujuan'
                                     || fieldNameLower === 'disposisi_ke'
                                     || fieldNameLower === 'kepada_disposisi';

                if (isPenerimaField) {
                    html += `<select name="${name}" class="form-select form-select-lg" ${req} onchange="syncPenerima(this.value)">
                        <option value="">-- Pilih Penerima --</option>`;
                    usersData.forEach(user => {
                        if (user.id != currentUser.id) {
                            // ✅ PENTING: value = user.id (angka), BUKAN nama
                            html += `<option value="${user.id}">${user.nama_lengkap} - ${user.jabatan}</option>`;
                        }
                    });
                    html += `</select><small class="text-muted">Penerima akan menerima notifikasi</small>`;
                    
                } else if (fieldNameLower === 'penandatangan' || fieldNameLower === 'dari') {
                    html += `<input type="hidden" name="${name}" value="${currentUser.id}">
                        <div class="alert alert-light border d-flex align-items-center mb-0">
                            <i class="bi bi-person-check-fill me-2 text-success"></i>
                            <div>Otomatis: <strong>${currentUser.nama_lengkap}</strong> (${currentUser.jabatan})</div>
                        </div>`;
                        
                } else if (fieldNameLower.includes('isi') || fieldNameLower.includes('nota') || fieldNameLower.includes('ringkasan')) {
                    html += `<textarea name="${name}" class="form-control" rows="4" ${req} oninput="updatePreview()"></textarea>`;
                    
                } else if (fieldNameLower.includes('tanggal')) {
                    html += `<input type="date" name="${name}" class="form-control" ${req} onchange="updatePreview()">`;
                    
                } else {
                    html += `<input type="text" name="${name}" class="form-control" ${req} oninput="updatePreview()">`;
                }

                html += `</div></div></div>`;
            });

            fieldsContent.innerHTML = html + '</div>';
            btnSubmit.disabled = false;
            btnPreview.disabled = false;

        } catch (error) {
            console.error('Error load fields:', error);
            fieldsContent.innerHTML = `<div class="alert alert-danger">Gagal memuat form.</div>`;
        }
    });

    // Preview function
    window.updatePreview = function() {
        const templateId = templateSelect.value;
        const config = templateConfig[templateId];
        const nomorSurat = nomorSuratInput.value || '__________';
        const tglInput = document.getElementById('tanggal')?.value;
        const tanggal = tglInput ? new Date(tglInput).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) : '__________';

        let fieldsHtml = '';
        currentFields.forEach(field => {
            const input = document.querySelector(`[name="fields[${field.id}]"]`);
            let val = '-';
            if (input) {
                if (input.tagName === 'SELECT') {
                    val = input.options[input.selectedIndex]?.text || '-';
                } else {
                    val = input.value || '-';
                }
            }
            fieldsHtml += `<tr><td width="30%"><strong>${field.nama_field}</strong></td><td>: ${val}</td></tr>`;
        });

        document.getElementById('preview-content').innerHTML = `
            <div style="font-family:serif;padding:30px;background:white;color:black;">
                <div style="text-align:center;border-bottom:3px double black;padding-bottom:10px;margin-bottom:20px;">
                    <h4 style="margin:0">E-OFFICE PDAM</h4><small>Sistem Informasi Surat Digital</small>
                </div>
                <table style="width:100%;margin-bottom:20px;">
                    <tr><td width="20%">Nomor</td><td>: ${nomorSurat}</td></tr>
                    <tr><td>Perihal</td><td>: ${document.getElementById('perihal').value||'-'}</td></tr>
                </table>
                <table style="width:100%;border-collapse:collapse;">${fieldsHtml}</table>
                <div style="margin-top:50px;text-align:right;">
                    <p>Kota Contoh, ${tanggal}</p><br><br>
                    <strong>${currentUser.nama_lengkap}</strong><br><span>${currentUser.jabatan}</span>
                </div>
            </div>`;
    };

    btnPreview.addEventListener('click', () => {
        updatePreview();
        new bootstrap.Modal(document.getElementById('previewModal')).show();
    });

    // ✅ DEBUG: Log saat form submit
    document.getElementById('letterForm')?.addEventListener('submit', function(e) {
        const keUserId = keUserIdHidden?.value;
        console.log('📤 [SUBMIT] ke_user_id:', keUserId);
        if (!keUserId) {
            console.warn('⚠️ PERINGATAN: ke_user_id kosong! Pastikan pilih penerima dulu.');
        }
    });
});
</script>
@endsection