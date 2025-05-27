@extends('layouts.app') {{-- Atau layout spesifik mahasiswa jika ada, misal: layouts.mahasiswa --}}

@section('title', 'Daftar Dokumen Proyek Akhir Saya')

@section('content')
<div class="container py-4 mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0 h3">Dokumen Proyek Akhir Saya</h1>
        <a href="{{ route('mahasiswa.dokumen.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Submit Dokumen Baru
        </a>
    </div>

    @include('partials.alerts') {{-- Atau logika alert langsung --}}

    <div class="card shadow-sm">
        <div class="card-header">
            Histori Submit Dokumen
        </div>
        <div class="card-body">
            <div class="card shadow-sm">
        <div class="card-body">
            @if($dokumens->isEmpty())
                <div class="alert alert-info mb-0">
                    Anda belum pernah mensubmit dokumen apapun.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">No.</th>
                                <th scope="col">Jenis Dokumen</th>
                                <th scope="col">Nama File</th>
                                <th scope="col">Versi</th>
                                <th scope="col">Tgl Submit</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dokumens as $index => $dokumen)
                                <tr>
                                    <td>{{ $dokumens->firstItem() + $index }}</td>
                                    <td>{{ $dokumen->jenisDokumen->nama_jenis ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ Storage::url($dokumen->file_path) }}" target="_blank" title="Lihat/Unduh {{ $dokumen->nama_file_asli }}">
                                            {{ Str::limit($dokumen->nama_file_asli, 30) }}
                                        </a>
                                    </td>
                                    <td class="text-center">{{ $dokumen->versi }}</td>
                                    <td>{{ $dokumen->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        @php
                                            $statusClass = '';
                                            switch ($dokumen->status_review) {
                                                case 'pending': $statusClass = 'bg-warning text-dark'; break;
                                                case 'approved': $statusClass = 'bg-success'; break;
                                                case 'revision_needed': $statusClass = 'bg-danger'; break;
                                                case 'rejected': $statusClass = 'bg-secondary'; break;
                                            }
                                        @endphp
                                        <span class="badge rounded-pill {{ $statusClass }}">
                                            {{ Str::title(str_replace('_', ' ', $dokumen->status_review)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('mahasiswa.dokumen.show', $dokumen->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array($dokumen->status_review, ['pending', 'revision_needed']))
                                            {{-- Tombol Edit/Resubmit bisa ditambahkan di sini jika ada alur revisi --}}
                                            {{-- <a href="{{ route('mahasiswa.dokumen-proyek-akhir.edit', $dokumen->id) }}" class="btn btn-warning btn-sm" title="Revisi/Edit">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a> --}}
                                            <form action="{{ route('mahasiswa.dokumen.destroy', $dokumen->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen \'{{ $dokumen->nama_file_asli }}\' (Versi {{ $dokumen->versi }})?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($dokumens->hasPages())
                <div class="mt-3">
                    {{ $dokumens->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
