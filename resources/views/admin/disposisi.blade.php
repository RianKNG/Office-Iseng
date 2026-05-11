@extends('layouts.app')
@section('content')
<div class="container">
    <h2>📋 Semua Disposisi</h2>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary mb-3">← Kembali</a>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nomor Surat</th>
                    <th>Dari</th>
                    <th>Ke</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($disposisis as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->letter->nomor_surat ?? '-' }}</td>
                    <td>{{ $d->dari->nama_lengkap ?? '-' }}</td>
                    <td>{{ $d->ke->nama_lengkap ?? '-' }}</td>
                    <td><span class="badge bg-{{ $d->status == 'pending' ? 'warning' : 'success' }}">{{ $d->status }}</span></td>
                    <td>{{ $d->created_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $disposisis->links() }}
</div>
@endsection