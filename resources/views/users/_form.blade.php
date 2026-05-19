<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">NIK *</label>
        <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $user->nik ?? '') }}" required>
        @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Username *</label>
        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username ?? '') }}" required>
        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Email *</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Password {{ isset($user) ? '(kosongkan jika tidak diubah)' : '*' }}</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }}>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Nama Lengkap *</label>
        <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap', $user->nama_lengkap ?? '') }}" required>
        @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">No. HP</label>
        <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $user->no_hp ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Jabatan *</label>
        <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan', $user->jabatan ?? '') }}" required>
        @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Struktur *</label>
        <select name="struktur" class="form-select @error('struktur') is-invalid @enderror" required>
            <option value="">Pilih...</option>
            <option value="pusat" {{ old('struktur', $user->struktur ?? '') == 'pusat' ? 'selected' : '' }}>Pusat</option>
            <option value="cabang" {{ old('struktur', $user->struktur ?? '') == 'cabang' ? 'selected' : '' }}>Cabang</option>
        </select>
        @error('struktur')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Level *</label>
        <select name="level" class="form-select @error('level') is-invalid @enderror" required>
            @php $levels = ['admin','dirut','kabag','kasubag','kasie','staff']; @endphp
            @foreach($levels as $l)
                <option value="{{ $l }}" {{ old('level', $user->level ?? '') == $l ? 'selected' : '' }}>{{ ucfirst($l) }}</option>
            @endforeach
        </select>
        @error('level')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Unit Kerja *</label>
        <input type="text" name="unit_kerja" class="form-control @error('unit_kerja') is-invalid @enderror" value="{{ old('unit_kerja', $user->unit_kerja ?? '') }}" placeholder="keuangan, pelayanan, sdm, dll" required>
        @error('unit_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Status *</label>
        <select name="status" class="form-select" required>
            <option value="aktif" {{ old('status', $user->status ?? '') == 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ old('status', $user->status ?? '') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
    </div>

    {{-- <div class="col-md-6">
        <label class="form-label">Foto Profile</label>
        <input type="file" name="foto_profile" class="form-control" accept="image/*">
        @if(isset($user) && $user->foto_profile)
            <img src="{{ asset('storage/'.$user->foto_profile) }}" height="60" class="mt-2 rounded border">
        @endif
    </div> --}}
    <!-- Untuk Foto Profile -->
<div class="col-md-6">
    <label class="form-label">Foto Profile</label>
    <input type="file" name="foto_profile" class="form-control" accept="image/*">
    @if(isset($user) && $user->foto_profile)
        <img src="{{ asset('storage/'.$user->foto_profile) }}" 
             height="80" 
             class="mt-2 rounded border" 
             id="previewFoto"> {{-- ✅ Tambahkan ID ini --}}
    @endif
</div>

<!-- Untuk Signature -->
<div class="col-md-6">
    <label class="form-label">Tanda Tangan Digital</label>
    <input type="file" name="signature" class="form-control" accept="image/*">
    @if(isset($user) && $user->signature)
        <img src="{{ asset('storage/'.$user->signature) }}" 
             height="80" 
             class="mt-2 rounded border"
             id="previewSignature"> {{-- ✅ Tambahkan ID ini --}}
    @endif
</div>
    {{-- <div class="col-md-6">
        <label class="form-label">Tanda Tangan Digital</label>
        <input type="file" name="signature" class="form-control" accept="image/*">
        @if(isset($user) && $user->signature)
            <img src="{{ asset('storage/'.$user->signature) }}" height="60" class="mt-2 rounded border">
        @endif
    </div> --}}
</div>