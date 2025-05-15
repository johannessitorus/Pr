@extends('layouts.app')

@section('title', 'Detail Pengajuan Bimbingan')

@section('content')
<div class="container py-4 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Detail Pengajuan Bimbingan</h1>
                <a href="{{ route('mahasiswa.request-bimbingan.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>

            @include('partials.alerts')

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Pengajuan</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Dosen Pembimbing</dt>
                        <dd class="col-sm-8">{{ $requestBimbingan->dosen->user->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Tanggal & Jam Usulan Mahasiswa</dt>
                        <dd class="col-sm-8">
                            {{ \Carbon\Carbon::parse($requestBimbingan->tanggal_usulan)->format('d F Y') }}
                            pukul {{ \Carbon\Carbon::parse($requestBimbingan->jam_usulan)->format('H:i') }}
                        </dd>

                        <dt class="col-sm-4">Topik/Agenda Bimbingan</dt>
                        <dd class="col-sm-8">{!! nl2br(e($requestBimbingan->topik_bimbingan)) !!}</dd>

                        @if($requestBimbingan->lokasi_usulan)
                        <dt class="col-sm-4">Lokasi Usulan</dt>
                        <dd class="col-sm-8">{{ $requestBimbingan->lokasi_usulan }}</dd>
                        @endif

                        @if($requestBimbingan->catatan_mahasiswa)
                        <dt class="col-sm-4">Catatan Mahasiswa</dt>
                        <dd class="col-sm-8">{!! nl2br(e($requestBimbingan->catatan_mahasiswa)) !!}</dd>
                        @endif

                        <dt class="col-sm-4">Status Pengajuan</dt>
                        <dd class="col-sm-8">
                            <span class="badge fs-6
                                @switch($requestBimbingan->status_request)
                                    @case('pending') bg-warning text-dark @break
                                    @case('approved') bg-success @break
                                    @case('rejected') bg-danger @break
                                    @case('rescheduled') bg-info text-dark @break
                                    @case('completed') bg-primary @break
                                    @case('cancelled') bg-secondary @break
                                    @default bg-light text-dark @break
                                @endswitch
                            ">
                                {{ ucfirst(str_replace('_', ' ', $requestBimbingan->status_request)) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Tanggal Diajukan</dt>
                        <dd class="col-sm-8">{{ $requestBimbingan->created_at->format('d F Y, H:i') }}</dd>

                        @if($requestBimbingan->status_request == 'rejected' && $requestBimbingan->catatan_dosen)
                            <hr class="my-3">
                            <dt class="col-sm-4 text-danger">Alasan Penolakan/Catatan Dosen</dt>
                            <dd class="col-sm-8 text-danger">{!! nl2br(e($requestBimbingan->catatan_dosen)) !!}</dd>
                        @elseif($requestBimbingan->status_request == 'rescheduled')
                            <hr class="my-3">
                            <dt class="col-sm-4 text-info">Jadwal Pengganti dari Dosen</dt>
                            <dd class="col-sm-8 text-info">
                                @if($requestBimbingan->tanggal_dosen && $requestBimbingan->jam_dosen)
                                    {{ \Carbon\Carbon::parse($requestBimbingan->tanggal_dosen)->format('d F Y') }}
                                    pukul {{ \Carbon\Carbon::parse($requestBimbingan->jam_dosen)->format('H:i') }}
                                @else
                                    Menunggu konfirmasi jadwal dari dosen.
                                @endif
                            </dd>
                            @if($requestBimbingan->catatan_dosen)
                            <dt class="col-sm-4 text-info pt-2">Catatan Dosen (Reschedule)</dt>
                            <dd class="col-sm-8 text-info pt-2">{!! nl2br(e($requestBimbingan->catatan_dosen)) !!}</dd>
                            @endif
                        @elseif($requestBimbingan->catatan_dosen) {{-- Untuk status lain yang mungkin punya catatan dosen --}}
                            <hr class="my-3">
                            <dt class="col-sm-4">Catatan Dosen</dt>
                            <dd class="col-sm-8">{!! nl2br(e($requestBimbingan->catatan_dosen)) !!}</dd>
                        @endif
                    </dl>
                </div>
                 @if($requestBimbingan->status_request == 'pending')
                <div class="card-footer text-end">
                    <a href="{{ route('mahasiswa.request-bimbingan.edit', $requestBimbingan->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit Pengajuan
                    </a>
                    <form action="{{ route('mahasiswa.request-bimbingan.destroy', $requestBimbingan->id) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan bimbingan ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-times-circle me-1"></i> Batalkan Pengajuan
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
