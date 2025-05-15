@extends('layouts.app') {{-- Sesuaikan dengan nama layout utama dosen Anda --}}

@section('title', 'Detail History Bimbingan')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('dosen.history-bimbingan.index', ['status_filter' => $historyBimbingan->status_kehadiran]) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar (Filter: {{ ucfirst(str_replace('_', ' ', $historyBimbingan->status_kehadiran)) }})
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                Bimbingan Mahasiswa: {{ $historyBimbingan->mahasiswa && $historyBimbingan->mahasiswa->user ? $historyBimbingan->mahasiswa->user->name : 'N/A' }}
                (Pertemuan Ke-{{ $historyBimbingan->pertemuan_ke }})
            </h6>
            {{-- Tombol Edit hanya muncul jika status masih bisa diubah atau dosen perlu menambah catatan --}}
            @if(in_array($historyBimbingan->status_kehadiran, ['terjadwal', 'hadir']))
            <a href="{{ route('dosen.history-bimbingan.edit', $historyBimbingan->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Proses/Update Status
            </a>
            @elseif($historyBimbingan->status_kehadiran == 'selesai' && empty($historyBimbingan->catatan_dosen))
             <a href="{{ route('dosen.history-bimbingan.edit', $historyBimbingan->id) }}" class="btn btn-success btn-sm">
                <i class="fas fa-comment-dots"></i> Tambah Catatan
            </a>
            @endif
        </div>
        <div class="card-body">
            @include('partials.alerts')

            <div class="row mb-2">
                <label class="col-sm-3 col-form-label fw-bold">Tanggal Bimbingan:</label>
                <div class="col-sm-9">
                    <p class="form-control-plaintext">{{ $historyBimbingan->tanggal_bimbingan->format('d F Y, H:i') }} WIB</p>
                </div>
            </div>

            <div class="row mb-2">
                <label class="col-sm-3 col-form-label fw-bold">Topik Awal (dari Request):</label>
                <div class="col-sm-9">
                    <p class="form-control-plaintext">{{ $historyBimbingan->requestBimbingan->topik_bimbingan ?? $historyBimbingan->topik }}</p>
                </div>
            </div>

            <div class="row mb-2">
                <label class="col-sm-3 col-form-label fw-bold">Status Kehadiran:</label>
                <div class="col-sm-9">
                    <p class="form-control-plaintext">
                        <span class="badge fs-6
                            @if($historyBimbingan->status_kehadiran == 'hadir' || $historyBimbingan->status_kehadiran == 'selesai') bg-success
                            @elseif(in_array($historyBimbingan->status_kehadiran, ['tidak_hadir_mahasiswa', 'tidak_hadir_dosen', 'dibatalkan_mahasiswa', 'dibatalkan_dosen'])) bg-danger
                            @elseif($historyBimbingan->status_kehadiran == 'terjadwal' || $historyBimbingan->status_kehadiran == 'dijadwalkan_ulang') bg-info text-dark
                            @else bg-secondary @endif">
                            {{ ucfirst(str_replace('_', ' ', $historyBimbingan->status_kehadiran)) }}
                        </span>
                    </p>
                </div>
            </div>

            @if($historyBimbingan->catatan_mahasiswa)
            <div class="row mb-2">
                <label class="col-sm-3 col-form-label fw-bold">Catatan Awal Mahasiswa:</label>
                <div class="col-sm-9">
                    <div class="form-control-plaintext bg-light p-2 rounded" style="white-space: pre-wrap;">{{ $historyBimbingan->catatan_mahasiswa }}</div>
                </div>
            </div>
            @endif

            @if($historyBimbingan->catatan_dosen)
            <div class="row mb-2">
                <label class="col-sm-3 col-form-label fw-bold">Catatan Dosen:</label>
                <div class="col-sm-9">
                    <div class="form-control-plaintext bg-light p-2 rounded" style="white-space: pre-wrap;">{{ $historyBimbingan->catatan_dosen }}</div>
                </div>
            </div>
            @else
                @if(in_array($historyBimbingan->status_kehadiran, ['hadir', 'selesai']))
                <div class="alert alert-warning">Belum ada catatan dari Anda untuk sesi bimbingan ini.</div>
                @endif
            @endif

            {{-- Tampilkan informasi dari RequestBimbingan jika ada --}}
            @if($historyBimbingan->requestBimbingan)
                <h5 class="mt-4">Detail dari Request Awal</h5>
                <div class="row mb-2">
                    <label class="col-sm-3 col-form-label">Tanggal Usulan Mahasiswa:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($historyBimbingan->requestBimbingan->tanggal_usulan)->format('d M Y') }}, Jam {{ $historyBimbingan->requestBimbingan->jam_usulan }}</p>
                    </div>
                </div>
                @if($historyBimbingan->requestBimbingan->lokasi_usulan)
                <div class="row mb-2">
                    <label class="col-sm-3 col-form-label">Lokasi Usulan Mahasiswa:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext">{{ $historyBimbingan->requestBimbingan->lokasi_usulan }}</p>
                    </div>
                </div>
                @endif
            @endif

        </div>
    </div>
</div>
@endsection
