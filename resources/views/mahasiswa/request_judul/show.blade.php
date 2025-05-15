@extends('layouts.app')

@section('title', 'Detail Pengajuan Judul')

@section('content')
<div class="container py-4 mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Detail Pengajuan Judul</h1>
        <a href="{{ route('mahasiswa.request-judul.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>

    @include('partials.alerts') {{-- Untuk menampilkan session success/error jika ada redirect dari update/delete --}}

    <div class="card shadow-sm">
        <div class="card-header">
           <h5 class="mb-0"> Judul: <strong>{{ $requestJudul->judul_diajukan }}</strong></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6 class="text-muted">Deskripsi:</h6>
                    <p style="white-space: pre-wrap;">{{ $requestJudul->deskripsi }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">Detail Pengajuan:</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted d-block">Diajukan oleh</small>
                                {{ $requestJudul->mahasiswa->user->name }} ({{ $requestJudul->mahasiswa->nim }})
                            </div>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                             <div>
                                <small class="text-muted d-block">Program Studi</small>
                                {{ $requestJudul->mahasiswa->prodi->nama_prodi ?? 'N/A' }}
                            </div>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted d-block">Dosen Tujuan</small>
                                {{ $requestJudul->dosenTujuan->user->name ?? 'N/A' }}
                            </div>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted d-block">Tanggal Pengajuan</small>
                                {{ $requestJudul->created_at->format('d F Y, H:i') }} WIB
                            </div>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted d-block">Status</small>
                                <span class="badge
                                    @switch($requestJudul->status)
                                        @case('pending') bg-warning text-dark @break
                                        @case('approved') bg-success @break
                                        @case('rejected') bg-danger @break
                                        @case('revisi') bg-info @break
                                        @default bg-secondary @break
                                    @endswitch
                                ">
                                    {{ ucwords(str_replace('_', ' ', $requestJudul->status)) }}
                                </span>
                            </div>
                        </li>
                        @if($requestJudul->status === 'rejected' || $requestJudul->status === 'approved' || $requestJudul->status === 'revisi')
                            <li class="list-group-item px-0">
                                <small class="text-muted d-block">Catatan Dosen:</small>
                                @if($requestJudul->catatan_dosen)
                                    <p class="mb-0 mt-1 p-2 bg-light border rounded" style="white-space: pre-wrap;">{{ $requestJudul->catatan_dosen }}</p>
                                @else
                                    <p class="mb-0 mt-1 text-muted fst-italic">Tidak ada catatan.</p>
                                @endif
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted d-flex justify-content-between align-items-center">
            <span>Terakhir diperbarui: {{ $requestJudul->updated_at->diffForHumans() }}</span>
            <div>
                @if($requestJudul->status === 'pending' || $requestJudul->status === 'revisi')
                    <a href="{{ route('mahasiswa.request-judul.edit', $requestJudul->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit Pengajuan
                    </a>
                @endif
                @if($requestJudul->status === 'pending')
                    <form action="{{ route('mahasiswa.request-judul.destroy', $requestJudul->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan judul ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Batalkan Pengajuan">
                            <i class="fas fa-trash-alt me-1"></i> Batalkan
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
