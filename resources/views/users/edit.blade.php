@extends('layouts.app')
@section('content')
<div class="container py-4">
    <!-- Header dengan Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">✏️ Edit User: {{ $user->nama_lengkap }}</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">User</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Alert Global Errors -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Terjadi {{ count($errors) }} error:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Alert Success -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">📋 Informasi User</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" id="formUser">
                @csrf @method('PUT')
                
                @include('users._form', ['user' => $user])
                
                <hr class="my-4">
                
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-warning" id="btnSubmit">
                        <i class="bi bi-save"></i> <span id="btnText">Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript: Preview Image & Loading State -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview Foto Profile
    const fotoInput = document.querySelector('input[name="foto_profile"]');
    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            previewImage(e, '#previewFoto');
        });
    }
    
    // Preview Signature
    const sigInput = document.querySelector('input[name="signature"]');
    if (sigInput) {
        sigInput.addEventListener('change', function(e) {
            previewImage(e, '#previewSignature');
        });
    }
    
    // Loading state saat submit
    const form = document.getElementById('formUser');
    const btn = document.getElementById('btnSubmit');
    const btnText = document.getElementById('btnText');
    
    if (form && btn) {
        form.addEventListener('submit', function() {
            btn.disabled = true;
            btnText.textContent = 'Menyimpan...';
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Menyimpan...';
        });
    }
});

// Helper: Preview image upload
function previewImage(event, previewSelector) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.querySelector(previewSelector);
            if (!preview) {
                preview = document.createElement('img');
                preview.id = previewSelector.replace('#', '');
                preview.className = 'mt-2 rounded border';
                preview.style.height = '80px';
                preview.style.maxWidth = '100%';
                event.target.closest('.col-md-6').appendChild(preview);
            }
            preview.src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endpush
@endsection