@extends('layouts.app') {{-- Atau layout spesifik dosen --}}

@section('title', 'Detail Request Bimbingan')

@section('content')
<div class="container py-4 mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Detail Request Bimbingan</h1>
                <a href="{{ route('dosen.request-bimbingan.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>

            @include('partials.alerts')

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Request dari Mahasiswa</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nama Mahasiswa</dt>
                        <dd class="col-sm-8">{{ $requestBimbingan->mahasiswa->user->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">NIM</dt>
                        <dd class="col-sm-8">{{ $requestBimbingan->mahasiswa->nim ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Program Studi</dt>
                        <dd class="col-sm-8">{{ $requestBimbingan->mahasiswa->prodi->nama_prodi ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Tanggal & Jam Usulan Mahasiswa</dt>
                        <dd class="col-sm-8">
                            {{ \Carbon\Carbon::parse($requestBimbingan->tanggal_usulan)->format('d F Y') }}
                            pukul {{ \Carbon\Carbon::parse($requestBimbingan->jam_usulan)->format('H:i') }}
                        </dd>

                        <dt class="col-sm-4">Topik/Agenda Bimbingan</dt>
                        <dd class="col-sm-8">{!! nl2br(e($requestBimbingan->topik_bimbingan)) !!}</dd>

                        @if($requestBimbingan->lokasi_usulan)
                        <dt class="col-sm-4">Lokasi Usulan Mahasiswa</dt>
                        <dd class="col-sm-8">{{ $requestBimbingan->lokasi_usulan }}</dd>
                        @endif

                        @if($requestBimbingan->catatan_mahasiswa)
                        <dt class="col-sm-4">Catatan dari Mahasiswa</dt>
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

                        {{-- Informasi dari Dosen jika sudah direspons --}}
                        @if($requestBimbingan->status_request != 'pending' && $requestBimbingan->status_request != 'cancelled')
                            <hr class="my-3">
                            <h5 class="mb-3">Respons Dosen</h5>
                            @if($requestBimbingan->status_request == 'rescheduled' && $requestBimbingan->tanggal_dosen)
                                <dt class="col-sm-4 text-info">Jadwal Disetujui/Reschedule</dt>
                                <dd class="col-sm-8 text-info">
                                    {{ \Carbon\Carbon::parse($requestBimbingan->tanggal_dosen)->format('d F Y') }}
                                    pukul {{ \Carbon\Carbon::parse($requestBimbingan->jam_dosen)->format('H:i') }}
                                </dd>
                            @elseif($requestBimbingan->status_request == 'approved')
                                <dt class="col-sm-4 text-success">Jadwal Disetujui Sesuai Usulan</dt>
                                <dd class="col-sm-8 text-success">
                                    {{ \Carbon\Carbon::parse($requestBimbingan->tanggal_usulan)->format('d F Y') }}
                                    pukul {{ \Carbon\Carbon::parse($requestBimbingan->jam_usulan)->format('H:i') }}
                                </dd>
                            @endif

                            @if($requestBimbingan->catatan_dosen)
                                <dt class="col-sm-4">Catatan Dosen</dt>
                                <dd class="col-sm-8">{!! nl2br(e($requestBimbingan->catatan_dosen)) !!}</dd>
                            @endif
                             <dt class="col-sm-4">Tanggal Respons</dt>
                            <dd class="col-sm-8">{{ $requestBimbingan->updated_at->format('d F Y, H:i') }}</dd>
                        @endif
                    </dl>
                </div>
                @if($requestBimbingan->status_request == 'pending')
                <div class="card-footer text-end">
                    <a href="{{ route('dosen.request-bimbingan.edit', $requestBimbingan->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Proses Pengajuan Ini
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
