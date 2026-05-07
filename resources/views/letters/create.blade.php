
@extends('layouts.app')
{{-- @extends('layout.v_template') --}}

@section('content')


    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📝 Buat Susssssrat Badddru</h5>
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


<!-- Modal Preview -->
<!-- Modal Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">👁️ Preview Surat</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="preview-content" class="p-4 bg-white" style="min-height: 400px;">
          <!-- Preview content akan dimuat di sini -->
        </div>
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
    let currentFields = [];
    let usersData = @json($users ?? []);
    const currentUser = @json(auth()->user()); // User yang login

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
            // Fallback manual
            const today = new Date();
            const bulan = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][today.getMonth()];
            const tahun = today.getFullYear();
            nomorSuratInput.value = `001/${templateCode}/${bulan}/${tahun}`;
        }
    }

    templateSelect.addEventListener('change', async function() {
        const templateId = this.value;
        const templateCode = this.options[this.selectedIndex]?.dataset.kode || '';

        if (!templateId) {
            dynamicFields.classList.add('d-none');
            btnSubmit.disabled = true;
            btnPreview.disabled = true;
            nomorSuratInput.value = '';
            return;
        }

        // Generate nomor surat otomatis
        await generateNomorSurat(templateCode);

        fieldsContent.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 mb-0">Memuat form...</p></div>';
        dynamicFields.classList.remove('d-none');
        btnSubmit.disabled = true;
        btnPreview.disabled = true;

        try {
            const response = await fetch(`/api/template/${templateId}/fields`);
            const fields = await response.json();

            if (!fields || fields.length === 0) {
                fieldsContent.innerHTML = '<p class="text-muted">Tidak ada field tambahan untuk template ini.</p>';
                btnSubmit.disabled = false;
                btnPreview.disabled = false;
                currentFields = [];
                return;
            }

            currentFields = fields;
            let html = '<div class="row g-3">';
            
            fields.forEach(field => {
                const req = field.is_required ? 'required' : '';
                const asterisk = field.is_required ? '<span class="text-danger">*</span>' : '';
                const name = `fields[${field.id}]`;
                
                html += `<div class="col-md-6">
                    <label class="form-label">${field.nama_field} ${asterisk}</label>`;

                // Field "kepada" di Surat Keluar → dropdown user
                if (field.nama_field.toLowerCase() === 'kepada' && templateId == 2) {
                    html += `<select name="${name}" class="form-select" ${req} onchange="updatePreview()">
                        <option value="">-- Pilih Penerima --</option>`;
                    
                    if (usersData && usersData.length > 0) {
                        usersData.forEach(user => {
                            html += `<option value="${user.nama_lengkap}" ${user.id == currentUser.id ? 'selected' : ''}>
                                ${user.nama_lengkap} - ${user.jabatan}
                            </option>`;
                        });
                    }
                    html += `</select>`;
                }
                // Field "kepada_nd" di Nota Dinas → dropdown user
                else if (field.nama_field.toLowerCase().includes('kepada') && templateId == 3) {
                    html += `<select name="${name}" class="form-select" ${req} onchange="updatePreview()">
                        <option value="">-- Pilih Penerima --</option>`;
                    
                    if (usersData && usersData.length > 0) {
                        usersData.forEach(user => {
                            // Exclude current user (tidak bisa kirim ke diri sendiri)
                            if (user.id != currentUser.id) {
                                html += `<option value="${user.nama_lengkap}">${user.nama_lengkap} - ${user.jabatan}</option>`;
                            }
                        });
                    }
                    html += `</select>`;
                }
                // Field "penandatangan" → auto-fill dengan user yang login
                else if (field.nama_field.toLowerCase() === 'penandatangan') {
                    html += `<input type="hidden" name="${name}" value="${currentUser.nama_lengkap}">
                             <input type="text" class="form-control" value="${currentUser.nama_lengkap} - ${currentUser.jabatan}" readonly>`;
                    html += `<small class="text-muted">Penandatangan: ${currentUser.nama_lengkap}</small>`;
                }
                // Field "dari" di Nota Dinas → auto-fill user login
                else if (field.nama_field.toLowerCase() === 'dari' && templateId == 3) {
                    html += `<input type="hidden" name="${name}" value="${currentUser.nama_lengkap}">
                             <input type="text" class="form-control" value="${currentUser.nama_lengkap} - ${currentUser.jabatan}" readonly>`;
                }
                else {
                    switch(field.tipe_field) {
                        case 'textarea':
                            html += `<textarea name="${name}" class="form-control" rows="3" ${req} oninput="updatePreview()"></textarea>`;
                            break;
                        case 'select':
                            html += `<select name="${name}" class="form-select" ${req} onchange="updatePreview()"><option value="">-- Pilih --</option>`;
                            if (field.opsi_json) {
                                try {
                                    const options = JSON.parse(field.opsi_json);
                                    if (Array.isArray(options)) {
                                        options.forEach(opt => {
                                            html += `<option value="${opt}">${opt}</option>`;
                                        });
                                    }
                                } catch(e) {}
                            }
                            html += `</select>`;
                            break;
                        case 'date':
                            html += `<input type="date" name="${name}" class="form-control" ${req} onchange="updatePreview()">`;
                            break;
                        case 'number':
                            html += `<input type="number" name="${name}" class="form-control" ${req} oninput="updatePreview()">`;
                            break;
                        case 'file':
                            html += `<input type="file" name="${name}" class="form-control" ${req}>`;
                            break;
                        default:
                            html += `<input type="text" name="${name}" class="form-control" ${req} oninput="updatePreview()">`;
                    }
                }

                html += `</div>`;
            });
            
            html += '</div>';
            fieldsContent.innerHTML = html;
            btnSubmit.disabled = false;
            btnPreview.disabled = false;

        } catch (error) {
            console.error('Error:', error);
            fieldsContent.innerHTML = '<p class="text-danger">❌ Gagal memuat form template.</p>';
        }
    });

  btnPreview.addEventListener('click', function() {
    // Cek apakah Bootstrap sudah ter-load
    if (typeof bootstrap === 'undefined') {
        alert('⚠️ Bootstrap JavaScript belum ter-load! Hubungi admin untuk perbaikan.');
        console.error('Bootstrap is not defined. Pastikan bootstrap.bundle.min.js ter-load di layout.');
        return;
    }
    
    updatePreview();
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    previewModal.show();
    });
    

    window.updatePreview = function() {
        const templateCode = templateSelect.options[templateSelect.selectedIndex]?.dataset.kode || 'XXX';
        const nomorSurat = nomorSuratInput.value || '_______________';
        const tanggal = document.getElementById('tanggal').value ? 
            new Date(document.getElementById('tanggal').value).toLocaleDateString('id-ID', {day: 'numeric', year: 'numeric', month: 'long'}) : '_______________';
        const perihal = document.getElementById('perihal').value || '_______________';

        let fieldsHtml = '';
        if (currentFields.length > 0) {
            currentFields.forEach(field => {
                const fieldName = `fields[${field.id}]`;
                const input = document.querySelector(`[name="${fieldName}"]`);
                let value = '';
                
                if (input) {
                    if (input.tagName === 'SELECT') {
                        value = input.options[input.selectedIndex]?.text || '-';
                        if (value === '-- Pilih --' || value === '-- Pilih Penerima --') value = '-';
                    } else if (input.type === 'file') {
                        value = input.files.length > 0 ? input.files[0].name : '-';
                    } else if (input.hasAttribute('readonly') && field.nama_field.toLowerCase() !== 'penandatangan' && field.nama_field.toLowerCase() !== 'dari') {
                        // Skip readonly fields (auto-filled)
                        return;
                    } else {
                        value = input.value || '-';
                    }
                }

                fieldsHtml += `
                    <tr>
                        <td width="30%" class="align-top"><strong>${field.nama_field}:</strong></td>
                        <td>${value}</td>
                    </tr>
                `;
            });
        }

        let suratTitle = '';
        let footerText = '';
        
        if (templateCode === 'SM-UMUM') {
            suratTitle = 'SURAT MASUK';
            footerText = 'Mengetahui,<br>Kepala Bagian Tata Usaha';
        } else if (templateCode === 'SK-RESMI') {
            suratTitle = 'SURAT KELUAR';
            footerText = `Hormat kami,<br>${currentUser.nama_lengkap}<br>${currentUser.jabatan}`;
        } else if (templateCode === 'ND-INT') {
            suratTitle = 'NOTA DINAS';
            footerText = `Demikian nota dinas ini disampaikan,<br>${currentUser.nama_lengkap}<br>${currentUser.jabatan}`;
        }

        const previewHtml = `
            <div style="font-family: 'Times New Roman', Times, serif; line-height: 1.6; max-width: 800px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 25px; border-bottom: 4px double #000; padding-bottom: 15px;">
                    <h2 style="margin: 0; font-size: 22px; font-weight: bold;">PEMERINTAH KOTA CONTOH</h2>
                    <h3 style="margin: 8px 0 5px; font-size: 18px; font-weight: bold;">DINAS KOMUNIKASI DAN INFORMATIKA</h3>
                    <p style="margin: 3px 0; font-size: 13px;">Jl. Teknologi No. 123, Kota Contoh - Telp: (021) 123-4567</p>
                </div>

                <div style="text-align: center; margin-bottom: 25px;">
                    <h4 style="margin: 0; font-size: 16px; text-decoration: underline; text-underline-offset: 5px;">${suratTitle}</h4>
                    <p style="margin: 5px 0 0; font-size: 14px;">Nomor: ${nomorSurat}</p>
                </div>

                <div style="margin-bottom: 25px;">
                    <table style="width: 100%; font-size: 14px;">
                        <tr><td style="width: 140px;"><strong>Kode</strong></td><td>: ${templateCode}</td></tr>
                        <tr><td><strong>Nomor</strong></td><td>: ${nomorSurat}</td></tr>
                        <tr><td><strong>Tanggal</strong></td><td>: ${tanggal}</td></tr>
                        <tr><td><strong>Perihal</strong></td><td>: ${perihal}</td></tr>
                    </table>
                </div>

                ${fieldsHtml ? `
                <div style="margin-bottom: 30px;">
                    <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                        ${fieldsHtml}
                    </table>
                </div>
                ` : ''}

                <div style="margin-top: 50px; text-align: right; padding-right: 20px;">
                    <p style="margin: 0;">Kota Contoh, ${tanggal}</p>
                    <p style="margin: 40px 0 10px;">${footerText}</p>
                    <p style="margin: 60px 0 5px;"><strong>_________________________</strong></p>
                    <p style="margin: 0;">NIP. ${currentUser.nip || '___________________'}</p>
                </div>
            </div>
        `;

        document.getElementById('preview-content').innerHTML = previewHtml;
    };
});
</script>
@endsection