@extends('layouts.app')

@section('content')
@push('styles')
<style>
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
    }
    
    .input-group .form-control, .input-group .form-select {
        border-left: none;
    }
    
    .input-group .form-control:focus, .input-group .form-select:focus {
        border-left: none;
    }
    
    #preview-content {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .template-header {
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .spinner-border {
        width: 3rem;
        height: 3rem;
    }
    
    .alert {
        border-radius: 10px;
    }
    
    .btn {
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
        border: none;
    }
    
    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: none;
    }
</style>
@endpush
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
                                    value="{{ old('nomor_surat') }}" readonly 
                                    placeholder="Auto-generate">
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
                            <button type="button" id="btnPreview" class="btn btn-info me-md-2" disabled>
                                👁️ Preview Surat
                            </button>
                            <button type="submit" id="btnSubmit" class="btn btn-success px-4" disabled>💾 Simpan Surat</button>
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
                <div id="preview-content" class="p-4 border rounded bg-white">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    🖨️ Cetak
                </button>
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
    let currentFields = [];
    let usersData = @json($users ?? []);
    const currentUser = @json(auth()->user());

    // Konfigurasi template
    const templateConfig = {
        1: { // Surat Masuk
            name: 'SM-UMUM',
            title: 'SURAT MASUK',
            icon: '📥',
            color: 'info',
            fields: ['nomor_surat_pengirim', 'tanggal_surat_pengirim', 'pengirim', 'isi_ringkas', 'disposisi']
        },
        2: { // Surat Keluar Resmi
            name: 'SK-RESMI',
            title: 'SURAT KELUAR',
            icon: '📤',
            color: 'success',
            fields: ['kepada', 'jabatan_penerima', 'isi_surat', 'penandatangan']
        },
        3: { // Nota Dinas Internal
            name: 'ND-INT',
            title: 'NOTA DINAS',
            icon: '📝',
            color: 'warning',
            fields: ['dari', 'kepada_nd', 'perihal_nd', 'isi_nota']
        }
    };

    // Generate nomor surat otomatis
    async function generateNomorSurat(templateCode) {
        try {
            const response = await fetch(`/api/generate-nomor-surat?kode=${templateCode}`);
            const data = await response.json();
            if (data.nomor) {
                nomorSuratInput.value = data.nomor;
            }
        } catch (error) {
            console.error('Error generate nomor:', error);
            const today = new Date();
            const bulan = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][today.getMonth()];
            const tahun = today.getFullYear();
            nomorSuratInput.value = `001/${templateCode}/${bulan}/${tahun}`;
        }
    }

    // Render template header
    function renderTemplateHeader(templateId) {
        const config = templateConfig[templateId];
        if (!config) return '';

        const gradientClass = config.color === 'success' ? 'bg-gradient-success' : 
                             config.color === 'warning' ? 'bg-gradient-warning' : 'bg-gradient-info';
        
        return `
            <div class="template-header mb-4 p-4 rounded-3 ${gradientClass} text-white shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="display-4 me-3">${config.icon}</div>
                    <div>
                        <h4 class="mb-1 fw-bold">${config.title}</h4>
                        <p class="mb-0 opacity-75">${config.name}</p>
                    </div>
                </div>
            </div>
        `;
    }

    templateSelect.addEventListener('change', async function() {
        const templateId = this.value;
        const templateCode = this.options[this.selectedIndex]?.dataset.kode || '';
        const config = templateConfig[templateId];

        if (!templateId) {
            dynamicFields.classList.add('d-none');
            btnSubmit.disabled = true;
            btnPreview.disabled = true;
            nomorSuratInput.value = '';
            return;
        }

        // Generate nomor surat otomatis
        await generateNomorSurat(templateCode);

        // Render header template
        let html = renderTemplateHeader(templateId);
        html += '<div class="row g-3">';
        
        fieldsContent.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-3 mb-0">Memuat form...</p></div>';
        dynamicFields.classList.remove('d-none');
        btnSubmit.disabled = true;
        btnPreview.disabled = true;

        try {
            const response = await fetch(`/api/template/${templateId}/fields`);
            const fields = await response.json();

            if (!fields || fields.length === 0) {
                fieldsContent.innerHTML = `
                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>Tidak ada field tambahan untuk template ini.</div>
                    </div>
                `;
                btnSubmit.disabled = false;
                btnPreview.disabled = false;
                currentFields = [];
                return;
            }

            currentFields = fields;
            
            fields.forEach(field => {
                const req = field.is_required ? 'required' : '';
                const asterisk = field.is_required ? '<span class="text-danger">*</span>' : '';
                const name = `fields[${field.id}]`;
                const fieldName = field.nama_field.toLowerCase();
                
                // Card wrapper untuk setiap field
                html += `<div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <label class="form-label fw-bold mb-2">
                                ${field.nama_field} ${asterisk}
                            </label>`;

                // SURAT KELUAR (Template 2)
                if (templateId == 2) {
                    if (fieldName === 'kepada') {
                        html += `
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <select name="${name}" class="form-select form-select-lg" ${req} onchange="updatePreview()">
                                    <option value="">-- Pilih Penerima Surat --</option>`;
                        
                        if (usersData && usersData.length > 0) {
                            usersData.forEach(user => {
                                const selected = user.id == currentUser.id ? 'selected' : '';
                                html += `<option value="${user.nama_lengkap}" ${selected}>
                                    ${user.nama_lengkap} - ${user.jabatan}
                                </option>`;
                            });
                        }
                        html += `</select>
                            </div>
                            <div class="form-text">Pilih penerima surat dari daftar pegawai</div>`;
                    }
                    else if (fieldName === 'jabatan_penerima') {
                        html += `
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                <input type="text" name="${name}" class="form-control" placeholder="Jabatan penerima" ${req}>
                            </div>`;
                    }
                    else if (fieldName === 'isi_surat') {
                        html += `
                            <textarea name="${name}" class="form-control" rows="5" ${req} oninput="updatePreview()" 
                                placeholder="Tulis isi surat di sini..."></textarea>
                            <div class="form-text">Masukkan isi surat yang akan dikirim</div>`;
                    }
                    else if (fieldName === 'penandatangan') {
                        html += `
                            <input type="hidden" name="${name}" value="${currentUser.nama_lengkap}">
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <div>Penandatangan: <strong>${currentUser.nama_lengkap}</strong> (${currentUser.jabatan})</div>
                            </div>`;
                    }
                    else {
                        html += `<input type="text" name="${name}" class="form-control" ${req}>`;
                    }
                }
                // NOTA DINAS (Template 3)
                else if (templateId == 3) {
                    if (fieldName === 'dari') {
                        html += `
                            <input type="hidden" name="${name}" value="${currentUser.nama_lengkap}">
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div>Dari: <strong>${currentUser.nama_lengkap}</strong> - ${currentUser.jabatan}</div>
                            </div>`;
                    }
                    else if (fieldName === 'kepada_nd') {
                        html += `
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-check"></i></span>
                                <select name="${name}" class="form-select form-select-lg" ${req} onchange="updatePreview()">
                                    <option value="">-- Pilih Penerima Nota Dinas --</option>`;
                        
                        if (usersData && usersData.length > 0) {
                            usersData.forEach(user => {
                                if (user.id != currentUser.id) {
                                    html += `<option value="${user.nama_lengkap}">
                                        ${user.nama_lengkap} - ${user.jabatan}
                                    </option>`;
                                }
                            });
                        }
                        html += `</select>
                            </div>
                            <div class="form-text">Penerima nota dinas (tidak bisa diri sendiri)</div>`;
                    }
                    else if (fieldName === 'perihal_nd' || fieldName === 'isi_nota') {
                        const rows = fieldName === 'isi_nota' ? '6' : '2';
                        const placeholder = fieldName === 'isi_nota' ? 'Tulis isi nota dinas secara detail...' : 'Ringkasan perihal';
                        html += `
                            <textarea name="${name}" class="form-control" rows="${rows}" ${req} oninput="updatePreview()" 
                                placeholder="${placeholder}"></textarea>`;
                    }
                    else {
                        html += `<input type="text" name="${name}" class="form-control" ${req}>`;
                    }
                }
                // SURAT MASUK (Template 1)
                else if (templateId == 1) {
                    if (fieldName === 'nomor_surat_pengirim') {
                        html += `
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                <input type="text" name="${name}" class="form-control" placeholder="Nomor surat dari pengirim" ${req}>
                            </div>`;
                    }
                    else if (fieldName === 'tanggal_surat_pengirim') {
                        html += `
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <input type="date" name="${name}" class="form-control" ${req}>
                            </div>`;
                    }
                    else if (fieldName === 'pengirim') {
                        html += `
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input type="text" name="${name}" class="form-control" placeholder="Instansi/Nama pengirim" ${req}>
                            </div>`;
                    }
                    else if (fieldName === 'isi_ringkas') {
                        html += `
                            <textarea name="${name}" class="form-control" rows="4" ${req} 
                                placeholder="Ringkasan isi surat masuk..."></textarea>`;
                    }
                    else if (fieldName === 'disposisi') {
                        html += `
                            <textarea name="${name}" class="form-control" rows="3" 
                                placeholder="Catatan disposisi (opsional)..."></textarea>
                            <div class="form-text">Isi disposisi jika diperlukan</div>`;
                    }
                    else {
                        html += `<input type="text" name="${name}" class="form-control" ${req}>`;
                    }
                }
                else {
                    // Default field
                    switch(field.tipe_field) {
                        case 'textarea':
                            html += `<textarea name="${name}" class="form-control" rows="3" ${req}></textarea>`;
                            break;
                        case 'select':
                            html += `<select name="${name}" class="form-select" ${req}><option value="">-- Pilih --</option></select>`;
                            break;
                        case 'date':
                            html += `<input type="date" name="${name}" class="form-control" ${req}>`;
                            break;
                        default:
                            html += `<input type="text" name="${name}" class="form-control" ${req}>`;
                    }
                }

                html += `</div></div></div>`;
            });
            
            html += '</div>';
            fieldsContent.innerHTML = html;
            btnSubmit.disabled = false;
            btnPreview.disabled = false;

        } catch (error) {
            console.error('Error:', error);
            fieldsContent.innerHTML = `
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    <div>Gagal memuat form template. Silakan coba lagi.</div>
                </div>
            `;
        }
    });

    btnPreview.addEventListener('click', function() {
        updatePreview();
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        previewModal.show();
    });

    window.updatePreview = function() {
        const templateId = templateSelect.value;
        const config = templateConfig[templateId];
        const templateCode = config ? config.name : 'XXX';
        const nomorSurat = nomorSuratInput.value || '_______________';
        const tanggal = document.getElementById('tanggal').value ? 
            new Date(document.getElementById('tanggal').value).toLocaleDateString('id-ID', {day: 'numeric', year: 'numeric', month: 'long'}) : '_______________';
        const perihal = document.getElementById('perihal').value || '_______________';

        let fieldsHtml = '';
        if (currentFields.length > 0) {
            currentFields.forEach(field => {
                const fieldName = `fields[${field.id}]`;
                const input = document.querySelector(`[name="${fieldName}"]`);
                let value = '-';
                
                if (input) {
                    if (input.tagName === 'SELECT') {
                        value = input.options[input.selectedIndex]?.text || '-';
                        if (value.includes('-- Pilih')) value = '-';
                    } else if (input.type === 'textarea' || input.type === 'text' || input.type === 'date') {
                        value = input.value || '-';
                    }
                }

                fieldsHtml += `
                    <tr>
                        <td width="35%" class="align-top py-2"><strong>${field.nama_field}</strong></td>
                        <td class="py-2">: ${value}</td>
                    </tr>
                `;
            });
        }

        let suratTitle = config ? config.title : 'SURAT';
        let footerText = '';
        let headerContent = '';
        
        if (templateId == 1) { // Surat Masuk
            headerContent = `
                <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 30px;">
                    <h2 style="margin: 0; font-size: 24px;">SURAT MASUK</h2>
                    <p style="margin: 5px 0 0; opacity: 0.9;">${templateCode}</p>
                </div>
            `;
            footerText = 'Mengetahui,<br>Kepala Bagian Tata Usaha';
        } else if (templateId == 2) { // Surat Keluar
            headerContent = `
                <div style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 30px;">
                    <h2 style="margin: 0; font-size: 24px;">SURAT KELUAR</h2>
                    <p style="margin: 5px 0 0; opacity: 0.9;">${templateCode}</p>
                </div>
            `;
            footerText = `Hormat kami,<br><br><br><strong>${currentUser.nama_lengkap}</strong><br>${currentUser.jabatan}`;
        } else if (templateId == 3) { // Nota Dinas
            headerContent = `
                <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; padding: 20px; text-align: center; border-radius: 8px; margin-bottom: 30px;">
                    <h2 style="margin: 0; font-size: 24px;">NOTA DINAS</h2>
                    <p style="margin: 5px 0 0; opacity: 0.9;">${templateCode}</p>
                </div>
            `;
            footerText = `Demikian nota dinas ini disampaikan,<br><br><br><strong>${currentUser.nama_lengkap}</strong>`;
        }

        const previewHtml = `
            <div style="font-family: 'Times New Roman', Times, serif; line-height: 1.8; max-width: 800px; margin: 0 auto; padding: 40px; background: white;">
                ${headerContent}
                
                <div style="text-align: center; margin-bottom: 30px; border-bottom: 3px double #000; padding-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 22px; font-weight: bold;">PEMERINTAH KOTA CONTOH</h2>
                    <h3 style="margin: 10px 0 5px; font-size: 18px; font-weight: bold;">DINAS KOMUNIKASI DAN INFORMATIKA</h3>
                    <p style="margin: 5px 0; font-size: 13px;">Jl. Teknologi No. 123, Kota Contoh - Telp: (021) 123-4567</p>
                    <p style="margin: 0; font-size: 12px;">Email: diskominfo@kotacontoh.go.id | Website: www.diskominfo.kotacontoh.go.id</p>
                </div>

                <div style="margin-bottom: 30px;">
                    <table style="width: 100%; font-size: 14px;">
                        <tr><td style="width: 150px;"><strong>Nomor</strong></td><td>: ${nomorSurat}</td></tr>
                        <tr><td><strong>Tanggal</strong></td><td>: ${tanggal}</td></tr>
                        <tr><td><strong>Perihal</strong></td><td>: ${perihal}</td></tr>
                    </table>
                </div>

                ${fieldsHtml ? `
                <div style="margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid ${templateId == 2 ? '#28a745' : templateId == 3 ? '#ffc107' : '#17a2b8'};">
                    <table style="width: 100%; font-size: 14px;">
                        ${fieldsHtml}
                    </table>
                </div>
                ` : ''}

                <div style="margin-top: 50px; text-align: right; padding-right: 20px;">
                    <p style="margin: 0;">Kota Contoh, ${tanggal}</p>
                    <p style="margin: 60px 0 10px;">${footerText}</p>
                    <p style="margin: 0; font-size: 13px; color: #666;">NIP. ${currentUser.nip || '___________________'}</p>
                </div>
            </div>
        `;

        document.getElementById('preview-content').innerHTML = previewHtml;
    };
});
</script>
@endsection