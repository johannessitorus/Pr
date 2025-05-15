@extends('layouts.app')

@section('title', 'Manajemen Mahasiswa')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Manajemen Mahasiswa</h1>
        <a href="{{ route('admin.mahasiswa.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Mahasiswa
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">
            Daftar Mahasiswa
        </div>
        <div class="card-body">
            @if($mahasiswas->isEmpty())
                <p class="text-muted">Belum ada data mahasiswa.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>NIM</th>
                                <th>Email</th>
                                <th>Prodi</th>
                                <th>Angkatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Variabel iterasi diubah menjadi $mahasiswa --}}
                            @foreach($mahasiswas as $index => $mahasiswa)
                                <tr>
                                    <td>{{ $mahasiswas->firstItem() + $index }}</td>
                                    {{-- Akses data user melalui relasi $mahasiswa->user --}}
                                    <td>{{ $mahasiswa->user->name ?? '-' }}</td>
                                    {{-- Akses nim langsung dari $mahasiswa --}}
                                    <td>{{ $mahasiswa->nim ?? '-' }}</td>
                                    {{-- Akses data user melalui relasi $mahasiswa->user --}}
                                    <td>{{ $mahasiswa->user->email ?? '-' }}</td>
                                    {{-- Akses data prodi melalui relasi $mahasiswa->prodi --}}
                                    <td>{{ $mahasiswa->prodi->nama_prodi ?? '-' }}</td>
                                    {{-- Akses angkatan langsung dari $mahasiswa --}}
                                    <td>{{ $mahasiswa->angkatan ?? '-' }}</td>
                                    <td>
                                        {{-- Rute aksi menggunakan $mahasiswa->id --}}
                                        <a href="{{ route('admin.mahasiswa.show', $mahasiswa->id) }}" class="btn btn-sm btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('admin.mahasiswa.edit', $mahasiswa->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.mahasiswa.destroy', $mahasiswa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mahasiswa ini? Akun pengguna terkait juga akan dihapus.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $mahasiswas->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
