@extends('layouts.app') {{-- Sesuaikan dengan layout admin Anda --}}

@section('title', 'Manajemen Dosen')

@section('content')
<div class="container-fluid py-4 mt-4">
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Manajemen Dosen</h1>
    <p class="mb-4">Daftar semua dosen yang terdaftar dalam sistem.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
    @endif

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <a href="{{ route('admin.dosen.create') }}" class="btn btn-primary btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="text">Tambah Dosen Baru</span>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableDosen" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Dosen</th>
                            <th>NIDN/NIP</th>
                            <th>Email</th>
                            <th>Program Studi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dosens as $dosen)
                        <tr>
                            <td>{{ $loop->iteration + $dosens->firstItem() - 1 }}</td>
                            <td>{{ $dosen->user->name ?? 'N/A' }}</td>
                            <td>{{ $dosen->nidn }}</td>
                            <td>{{ $dosen->user->email ?? 'N/A' }}</td>
                            <td>{{ $dosen->prodi->nama_prodi ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.dosen.show', $dosen->id) }}" class="btn btn-sm btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.dosen.edit', $dosen->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.dosen.destroy', $dosen->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dosen ini? Tindakan ini juga akan menghapus akun user terkait.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data dosen.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $dosens->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Jika Anda menggunakan DataTables JS plugin
    // $(document).ready(function() {
    //     $('#dataTableDosen').DataTable();
    // });
</script>
@endpush
@endsection
