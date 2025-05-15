@extends('layouts.app') {{-- Atau layout dosen --}}

@section('title', 'Review Dokumen Mahasiswa')

@section('content')
<div class="container py-4 mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0 h3">Dokumen Mahasiswa untuk Direview</h1>
        {{-- Tombol kembali ke dashboard jika perlu --}}
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    @include('partials.alerts')

    <div class="card shadow-sm">
        <div class="card-body">
            @if($dokumensPending->isEmpty())
                <div class="alert alert-info mb-0">
                    Tidak ada dokumen yang menunggu review dari mahasiswa bimbingan Anda saat ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Mahasiswa (NIM)</th>
                                <th>Jenis Dokumen</th>
                                <th>File</th>
                                <th>Versi</th>
                                <th>Tgl Submit/Update</th>
                                <th>Status Saat Ini</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dokumensPending as $index => $dokumen)
                                <tr>
                                    <td>{{ $dokumensPending->firstItem() + $index }}</td>
                                    <td>
                                        {{ $dokumen->mahasiswa->user->name ?? 'N/A' }}<br>
                                        <small class="text-muted">{{ $dokumen->mahasiswa->nim ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $dokumen->jenisDokumen->nama_jenis ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ Storage::url($dokumen->file_path) }}" target="_blank" title="Lihat/Unduh {{ $dokumen->nama_file_asli }}">
                                            {{ Str::limit($dokumen->nama_file_asli, 25) }}
                                        </a>
                                    </td>
                                    <td class="text-center">{{ $dokumen->versi }}</td>
                                    <td>{{ $dokumen->updated_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        @php
                                            $statusClass = '';
                                            switch ($dokumen->status_review) {
                                                case 'pending': $statusClass = 'bg-warning text-dark'; break;
                                                case 'revision_needed': $statusClass = 'bg-danger'; break;
                                                default: $statusClass = 'bg-secondary'; break;
                                            }
                                        @endphp
                                        <span class="badge rounded-pill {{ $statusClass }}">
                                            {{ Str::title(str_replace('_', ' ', $dokumen->status_review)) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('dosen.review-dokumen.proses', $dokumen->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-search-plus me-1"></i> Proses Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($dokumensPending->hasPages())
                <div class="mt-3">
                    {{ $dokumensPending->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
