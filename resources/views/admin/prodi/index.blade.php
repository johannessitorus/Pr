{{-- Misal Anda punya layout admin: resources/views/layouts/admin.blade.php --}}
@extends('layouts.app') {{-- Sesuaikan dengan nama layout Anda --}}

@section('content')
<div class="container">
    <h1>Manajemen Prodi</h1>
    <a href="{{ route('admin.prodi.create') }}" class="btn btn-primary mb-3">Tambah Prodi</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Prodi</th>
                <th>Nama Prodi</th>
                <th>Fakultas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($prodis as $key => $prodi)
            <tr>
                <td>{{ $prodis->firstItem() + $key }}</td>
                <td>{{ $prodi->kode_prodi }}</td>
                <td>{{ $prodi->nama_prodi }}</td>
                <td>{{ $prodi->fakultas }}</td>
                <td>
                    <a href="{{ route('admin.prodi.edit', $prodi->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('admin.prodi.destroy', $prodi->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus prodi ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada data prodi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{ $prodis->links() }} {{-- Untuk Paginasi --}}
</div>
@endsection
