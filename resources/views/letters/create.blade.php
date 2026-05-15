@extends('layouts.app')

@section('content')
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
                            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('letters.store') }}" id="letterForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Hidden input untuk ke_user_id -->
                        <input type="hidden" name="ke_user_id" id="ke_user_id_hidden" value="{{ old('ke_user_id') }}">
                        
                        <!-- Indikator Penerima Terpilih -->
                        <div id="penerima-indicator" class="mt-3 p-3 bg-success bg-opacity-10 border border-success rounded d-none">
                            <i class="bi bi-person-check-fill text-success me-2"></i>
                            <strong>Penerima:</strong> <span id="penerima-nama"></span>
                            <small class="d-block text-muted" id="penerima-jabatan"></small>
                        </div>
                        
                        @error('ke_user_id')
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}
                            </div>
                        @enderror

                        <!-- Pilih Template -->
                        <div class="mb-3">
                            <label for="template_id" class="form-label fw-bold">Pilih Template Surat <span class="text-danger">*</span></label>
                            <select id="template_id" name="template_id" class="form-select @error('template_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis Surat --</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}" 
                                            data-jenis="{{ $tpl->jenis }}"
                                            data-kode="{{ $tpl->kode_template }}"
                                            {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                                        {{ $tpl->nama_template }} ({{ $tpl->kode_template }})
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pilih template untuk memuat field yang sesuai</small>
                        </div>

                        <!-- Field Standar (HANYA 1x, tidak duplikat) -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="nomor_surat" class="form-label fw-bold">Nomor Surat <span class="text-danger">*</span></label>
                                <input type="text" id="nomor_surat" name="nomor_surat" 
                                    class="form-control @error('nomor_surat') is-invalid @enderror" 
                                    value="{{ old('nomor_surat') }}" readonly placeholder="Auto-generate">
                                <small class="text-muted">Nomor akan digenerate otomatis setelah pilih template</small>
                                @error('nomor_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal" class="form-label fw-bold">Tanggal Surat <span class="text-danger">*</span></label>
                                <input type="date" id="tanggal" name="tanggal" 
                                    class="form-control @error('tanggal') is-invalid @enderror" 
                                    value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="perihal" class="form-label fw-bold">Perihal <span class="text-danger">*</span></label>
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
                            @error('file_path')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="reset" class="btn btn-secondary me-md-2">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </button>
                            <button type="button" id="btnPreview" class="btn btn-info me-md-2" disabled>
                                <i class="bi bi-eye me-1"></i>Preview
                            </button>
                            <button type="submit" id="btnSubmit" class="btn btn-success px-4" disabled>
                                <i class="bi bi-save me-1"></i>Simpan & Teruskan
                            </button>
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
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Cetak
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('template_id');
    const dynamicFields = document.getElementById('dynamic-fields');
    const fieldsContent = document.getElementById('fields-content');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnPreview = document.getElementById('btnPreview');
    const nomorSuratInput = document.getElementById('nomor_surat');
    const keUserIdHidden = document.getElementById('ke_user_id_hidden');
    
    let currentFields = [];
    // ✅ Pastikan controller kirim $usersData (bukan $users)
    let usersData = @json($usersData ?? []);
    const currentUser = @json(auth()->user());

    const templateConfig = {
        1: { name: 'SM-UMUM', title: 'SURAT MASUK', icon: '📥', color: 'info' },
        2: { name: 'SK-RESMI', title: 'SURAT KELUAR', icon: '📤', color: 'success' },
        3: { name: 'ND-INT', title: 'NOTA DINAS', icon: '📝', color: 'warning' }
    };

    function generateNomorSurat(templateCode) {
        fetch('/api/generate-nomor-surat?kode=' + encodeURIComponent(templateCode))
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.nomor) nomorSuratInput.value = data.nomor;
            })
            .catch(function(error) {
                console.error('Error generate nomor:', error);
                var today = new Date();
                var bulan = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][today.getMonth()];
                nomorSuratInput.value = '001/' + templateCode + '/' + bulan + '/' + today.getFullYear();
            });
    }

    // ✅ SYNC: Update hidden input + tampilkan detail penerima
    window.syncPenerima = function(userId) {
        if (keUserIdHidden) {
            keUserIdHidden.value = userId;
            console.log('✅ [SYNC] ke_user_id =', userId);

            if (userId && userId !== '') {
                btnSubmit.disabled = false;
                btnSubmit.title = "Siap disimpan";
                
                var selectedUser = usersData.find(function(u) { return u.id == userId; });
                if (selectedUser) {
                    document.getElementById('penerima-nama').textContent = selectedUser.nama_lengkap;
                    
                    // ✅ Format: "Kepala Cabang - Cabang Bandung"
                    var cabangInfo = selectedUser.cabang_nama ? ' - ' + selectedUser.cabang_nama : '';
                    document.getElementById('penerima-jabatan').textContent = 
                        selectedUser.jabatan + cabangInfo;
                }
                document.getElementById('penerima-indicator').classList.remove('d-none');
            } else {
                btnSubmit.disabled = true;
                btnSubmit.title = "Pilih penerima surat terlebih dahulu";
                document.getElementById('penerima-indicator').classList.add('d-none');
            }
        }
        updatePreview();
    };

    function renderTemplateHeader(templateId) {
        var config = templateConfig[templateId];
        if (!config) return '';
        var gradientClass = config.color === 'success' ? 'bg-gradient-success' : 
                           config.color === 'warning' ? 'bg-gradient-warning' : 'bg-gradient-info';
        return '<div class="template-header mb-4 p-4 rounded-3 ' + gradientClass + ' text-white shadow-sm">' +
            '<div class="d-flex align-items-center">' +
            '<div class="display-4 me-3">' + config.icon + '</div>' +
            '<div><h4 class="mb-1 fw-bold">' + config.title + '</h4><p class="mb-0 opacity-75">' + config.name + '</p></div>' +
            '</div></div>';
    }

    templateSelect.addEventListener('change', function() {
        var templateId = this.value;
        var selectedOption = this.options[this.selectedIndex];
        var templateCode = selectedOption ? selectedOption.dataset.kode : '';
        
        if (!templateId) {
            dynamicFields.classList.add('d-none');
            if (keUserIdHidden) keUserIdHidden.value = '';
            fieldsContent.innerHTML = '';
            btnPreview.disabled = true;
            btnSubmit.disabled = true;
            document.getElementById('penerima-indicator').classList.add('d-none');
            return;
        }

        generateNomorSurat(templateCode);
        
        var html = renderTemplateHeader(templateId);
        html += '<div class="row g-3">';
        
        fieldsContent.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
        dynamicFields.classList.remove('d-none');

        fetch('/api/template/' + templateId + '/fields')
            .then(function(response) { return response.json(); })
            .then(function(fields) {
                currentFields = fields || [];
                
                for (var i = 0; i < currentFields.length; i++) {
                    var field = currentFields[i];
                    var req = field.is_required ? 'required' : '';
                    var name = 'fields[' + field.id + ']';
                    var fieldNameLower = field.nama_field.toLowerCase().replace(/\s+/g, '');

                    html += '<div class="col-12">' +
                        '<div class="card border-0 shadow-sm mb-3">' +
                        '<div class="card-body">' +
                        '<label class="form-label fw-bold">' + field.nama_field + 
                        (field.is_required ? ' <span class="text-danger">*</span>' : '') + '</label>';

                    var isPenerimaField = fieldNameLower === 'kepada' 
                                         || fieldNameLower === 'kepada_nd' 
                                         || fieldNameLower === 'penerima' 
                                         || fieldNameLower === 'disposisi' 
                                         || fieldNameLower === 'tujuan'
                                         || fieldNameLower === 'disposisi_ke'
                                         || fieldNameLower === 'kepada_disposisi';

                    if (isPenerimaField) {
                        html += '<select name="' + name + '" class="form-select form-select-lg" ' + req + ' onchange="syncPenerima(this.value)">';
                        html += '<option value="">-- Pilih Penerima --</option>';
                        
                        // ✅ Render dropdown user: "Nama - Jabatan (Nama Cabang)"
                        for (var j = 0; j < usersData.length; j++) {
                            var user = usersData[j];
                            if (user.id != currentUser.id) {
                                var cabangInfo = user.cabang_nama ? ' (' + user.cabang_nama + ')' : '';
                                html += '<option value="' + user.id + '">' + 
                                        user.nama_lengkap + ' - ' + user.jabatan + cabangInfo +
                                        '</option>';
                            }
                        }
                        html += '</select><small class="text-muted">Penerima akan menerima notifikasi</small>';
                        
                    } else if (fieldNameLower === 'penandatangan' || fieldNameLower === 'dari') {
                        html += '<input type="hidden" name="' + name + '" value="' + currentUser.id + '">' +
                            '<div class="alert alert-light border d-flex align-items-center mb-0">' +
                            '<i class="bi bi-person-check-fill me-2 text-success"></i>' +
                            '<div>Otomatis: <strong>' + currentUser.nama_lengkap + '</strong> (' + currentUser.jabatan + ')</div>' +
                            '</div>';
                            
                    } else if (fieldNameLower.indexOf('isi') !== -1 || fieldNameLower.indexOf('nota') !== -1 || fieldNameLower.indexOf('ringkasan') !== -1) {
                        html += '<textarea name="' + name + '" class="form-control" rows="4" ' + req + ' oninput="updatePreview()"></textarea>';
                        
                    } else if (fieldNameLower.indexOf('tanggal') !== -1) {
                        html += '<input type="date" name="' + name + '" class="form-control" ' + req + ' onchange="updatePreview()">';
                        
                    } else {
                        html += '<input type="text" name="' + name + '" class="form-control" ' + req + ' oninput="updatePreview()">';
                    }

                    html += '</div></div></div>';
                }

                fieldsContent.innerHTML = html + '</div>';
                btnPreview.disabled = false;

                // Auto-enable btnSubmit jika tidak ada field penerima
                var hasPenerimaField = document.querySelector('select[onchange*="syncPenerima"]');
                if (!hasPenerimaField) {
                    btnSubmit.disabled = false;
                    btnSubmit.title = "Siap disimpan";
                } else {
                    btnSubmit.disabled = true;
                    btnSubmit.title = "Pilih penerima surat terlebih dahulu";
                }
                
            })
            .catch(function(error) {
                console.error('Error load fields:', error);
                fieldsContent.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Gagal memuat form. Silakan coba lagi.</div>';
            });
    });

    // Preview function
    window.updatePreview = function() {
        var templateId = templateSelect.value;
        var config = templateConfig[templateId];
        var nomorSurat = nomorSuratInput.value || '__________';
        var tglInput = document.getElementById('tanggal');
        var tanggal = tglInput && tglInput.value ? new Date(tglInput.value).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) : '__________';

        var fieldsHtml = '';
        for (var i = 0; i < currentFields.length; i++) {
            var field = currentFields[i];
            var selector = '[name="fields[' + field.id + ']"]';
            var input = document.querySelector(selector);
            var val = '-';
            if (input) {
                if (input.tagName === 'SELECT') {
                    val = input.options[input.selectedIndex] ? input.options[input.selectedIndex].text : '-';
                } else {
                    val = input.value || '-';
                }
            }
            fieldsHtml += '<tr><td width="30%"><strong>' + field.nama_field + '</strong></td><td>: ' + val + '</td></tr>';
        }

        var perihalInput = document.getElementById('perihal');
        var perihalVal = perihalInput ? (perihalInput.value || '-') : '-';

        document.getElementById('preview-content').innerHTML = 
            '<div style="font-family:serif;padding:30px;background:white;color:black;">' +
            '<div style="text-align:center;border-bottom:3px double black;padding-bottom:10px;margin-bottom:20px;">' +
            '<h4 style="margin:0">E-OFFICE PDAM</h4><small>Sistem Informasi Surat Digital</small>' +
            '</div>' +
            '<table style="width:100%;margin-bottom:20px;">' +
            '<tr><td width="20%">Nomor</td><td>: ' + nomorSurat + '</td></tr>' +
            '<tr><td>Perihal</td><td>: ' + perihalVal + '</td></tr>' +
            '</table>' +
            '<table style="width:100%;border-collapse:collapse;">' + fieldsHtml + '</table>' +
            '<div style="margin-top:50px;text-align:right;">' +
            '<p>Kota Contoh, ' + tanggal + '</p><br><br>' +
            '<strong>' + currentUser.nama_lengkap + '</strong><br><span>' + currentUser.jabatan + '</span>' +
            '</div></div>';
    };

    btnPreview.addEventListener('click', function() {
        updatePreview();
        var modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    });

    // Submit handler
    // ✅ SATU event listener submit saja
    document.getElementById('letterForm').addEventListener('submit', function(e) {
        var keUserId = keUserIdHidden ? keUserIdHidden.value : '';
        console.log('📤 [SUBMIT] ke_user_id:', keUserId);
        
        // ✅ CHECK: Apakah ada field penerima di form?
        var hasPenerimaField = document.querySelector('select[onchange*="syncPenerima"]');
        
        // ✅ Hanya validasi jika ada field penerima
        if (hasPenerimaField && (!keUserId || keUserId === '')) {
            e.preventDefault();
            alert('⚠️ Pilih penerima surat terlebih dahulu!');
            
            var penerimaSelect = document.querySelector('select[onchange*="syncPenerima"]');
            if (penerimaSelect) {
                penerimaSelect.scrollIntoView({behavior: 'smooth', block: 'center'});
                penerimaSelect.classList.add('is-invalid');
                penerimaSelect.focus();
            }
            return false;
        }
        
        console.log('✅ Form valid, submitting...');
    });
        
    // Reset handler
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        setTimeout(function() {
            dynamicFields.classList.add('d-none');
            fieldsContent.innerHTML = '';
            btnSubmit.disabled = true;
            btnPreview.disabled = true;
            if (keUserIdHidden) keUserIdHidden.value = '';
            document.getElementById('penerima-indicator').classList.add('d-none');
        }, 100);
    });
});
</script>
@endpush
@endsection