@extends('layouts.app') {{-- Sesuaikan --}}

@section('title', 'Kelola Dosen')

@section('content')
<div class="container-fluid py-4 mt-5">
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h1 class="mb-0">Kelola Dosen</h1>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('admin.dosen.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus me-1"></i> Tambah Dosen
            </a>
        </div>
    </div>

    @include('partials.alerts') {{-- Untuk pesan sukses/error --}}

    {{-- FORM FILTER --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Dosen</h5></div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dosen.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Cari Nama/Email/NIDN</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Masukkan kata kunci..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="prodi_id" class="form-label">Program Studi</label>
                        <select name="prodi_id" id="prodi_id" class="form-select">
                            <option value="">Semua Prodi</option>
                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-info w-100"><i class="fas fa-search me-1"></i> Terapkan</button>
                    </div>
                    <div class="col-md-auto">
                        <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary w-100"><i class="fas fa-sync-alt me-1"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- TABEL DAFTAR DOSEN --}}
    <div class="card shadow-sm">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Dosen</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>NIDN</th>
                            <th>Nama Dosen</th>
                            <th>Email</th>
                            <th>Program Studi</th>
                            <th>Spesialisasi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dosens as $dosen)
                        <tr>
                            <td>{{ $dosen->nidn }}</td>
                            <td>{{ $dosen->user->name ?? 'N/A' }}</td>
                            <td>{{ $dosen->user->email ?? 'N/A' }}</td>
                            <td>{{ $dosen->prodi->nama_prodi ?? 'N/A' }}</td>
                            <td>{{ Str::limit($dosen->spesialisasi, 50) ?: '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.dosen.edit', $dosen->id) }}" class="btn btn-sm btn-warning me-1" title="Edit Dosen">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.dosen.destroy', $dosen->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data dosen ini beserta akunnya? Aksi ini akan mengarsipkan data.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus Dosen">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>
                                Tidak ada data dosen ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($dosens->hasPages())
        <div class="card-footer bg-light">
            {{ $dosens->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
