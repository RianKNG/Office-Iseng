@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>👥 Manajemen User</h3>
        <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah User</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>NIK</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Level</th>
                        <th>Unit Kerja</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->nik }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($user->foto_profile)
                                    <img src="{{ asset('storage/'.$user->foto_profile) }}" width="35" height="35" class="rounded-circle me-2">
                                @else
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:35px;height:35px">{{ substr($user->nama_lengkap,0,1) }}</div>
                                @endif
                                {{ $user->nama_lengkap }}
                            </div>
                        </td>
                        <td>{{ $user->username }}</td>
                        <td><span class="badge bg-info">{{ ucfirst($user->level) }}</span></td>
                        <td>{{ ucfirst($user->unit_kerja) }}</td>
                        <td><span class="badge bg-{{ $user->status == 'aktif' ? 'success' : 'danger' }}">{{ ucfirst($user->status) }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada data user</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection