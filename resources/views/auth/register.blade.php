@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Registrasi Akun') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
        @error('username') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    {{-- <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap') }}" required>
        @error('nama_lengkap') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div> --}}

    <div class="form-group">
        <label>Email (Opsional)</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label>Jabatan</label>
        <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan', 'staff') }}" required>
        @error('jabatan') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label>Konfirmasi Password</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Daftar</button>
</form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection