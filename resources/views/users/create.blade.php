@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h3 class="mb-4">➕ Tambah User Baru</h3>
    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('users._form')
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection