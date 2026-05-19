@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h3 class="mb-4">✏️ Edit User: {{ $user->nama_lengkap }}</h3>
    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('users._form', ['user' => $user])
        <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg"></i> Update</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection