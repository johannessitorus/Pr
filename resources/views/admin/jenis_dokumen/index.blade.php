@extends('layouts.app')

@section('title', 'Manajemen Jenis Dokumen')

@section('content')
<div class="container py-4 mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0 h3">Manajemen Jenis Dokumen</h1>
        <a href="{{ route('admin.jenis-dokumen.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Jenis Dokumen
        </a>
    </div>

    @include('partials.alerts') {{-- Asumsi Anda punya partial untuk alert, atau pindahkan logika alert ke sini --}}

    <div class="card shadow-sm">
        <div class="card-header">
            Daftar Jenis Dokumen
        </div>
        <div class="card-body">
            @if($jenisDokumens->isEmpty())
                <div class="alert alert-info mb-0">
                    Belum ada data jenis dokumen yang ditambahkan.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nama Jenis Dokumen</th>
                                <th scope="col">Deskripsi</th>
                                <th scope="col" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jenisDokumens as $index => $jenis)
                                <tr>
                                    <td>{{ $jenisDokumens->firstItem() + $index }}</td>
                                    <td>{{ $jenis->nama_jenis }}</td>
                                    <td>{{ Str::limit($jenis->deskripsi, 80, '...') ?? '-' }}</td>
                                    <td class="text-center">
                                        {{-- Jika Anda memutuskan untuk memiliki halaman show --}}
                                        {{-- <a href="{{ route('admin.jenis-dokumen.show', $jenis->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a> --}}
                                        <a href="{{ route('admin.jenis-dokumen.edit', $jenis->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.jenis-dokumen.destroy', $jenis->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis dokumen \'{{ $jenis->nama_jenis }}\'? Tindakan ini tidak dapat diurungkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($jenisDokumens->hasPages())
                <div class="mt-3">
                    {{ $jenisDokumens->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
