@extends('layouts.app') {{-- Sesuaikan dengan layout admin Anda --}}

@section('title', $pageTitle ?? 'Kelola Pengguna')

@push('styles')
{{-- Jika ada style khusus untuk halaman ini --}}
<style>
    .table th, .table td {
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 mt-5">
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h1 class="mb-0">{{ $pageTitle ?? 'Kelola Pengguna' }}</h1>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Pengguna
            </a>
        </div>
    </div>

    @include('partials.alerts') {{-- Asumsi Anda punya partial untuk menampilkan session alerts --}}

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Pengguna</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Cari Nama/Username/Email</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Masukkan kata kunci..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="role" class="form-label">Peran</label>
                        <select name="role" id="role" class="form-select">
                            <option value="">Semua Peran (Terkait Halaman Ini)</option>
                            @if(isset($rolesForFilter))
                                @foreach($rolesForFilter as $roleValue)
                                    <option value="{{ $roleValue }}" {{ request('role') == $roleValue ? 'selected' : '' }}>
                                        {{ ucfirst($roleValue) }}
                                    </option>
                                @endforeach
                            @endif
                            {{-- Opsi untuk melihat dosen, jika ingin link dari sini (sudah dihandle redirect) --}}
                            {{-- <option value="dosen" {{ request('role') == 'dosen' ? 'selected' : '' }}>Dosen (Lihat Daftar Terpisah)</option> --}}
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-info w-100"><i class="fas fa-search me-1"></i> Terapkan</button>
                    </div>
                    <div class="col-md-auto">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary w-100"><i class="fas fa-sync-alt me-1"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Pengguna</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Bergabung Sejak</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'dosen' ? 'warning text-dark' : 'info') }}">
                                    {{ $user->role_name ?? ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning me-1" title="Edit Pengguna">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Akun ini akan diarsipkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus Pengguna" {{ $user->id === Auth::id() ? 'disabled' : '' }}>
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($users->hasPages())
        <div class="card-footer bg-light">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
{{-- Jika ada script khusus untuk halaman ini --}}
@endpush
