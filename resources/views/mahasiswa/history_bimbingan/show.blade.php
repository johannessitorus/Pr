@extends('layouts.app') {{-- Sesuaikan dengan nama layout utama mahasiswa Anda --}}

@section('title', 'Detail History Bimbingan')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('mahasiswa.history-bimbingan.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detail Bimbingan Pertemuan Ke-{{ $historyBimbingan->pertemuan_ke }}</h6>
        </div>
        <div class="card-body">
            {{-- Notifikasi Sukses/Error --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
             @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">Tanggal Bimbingan:</label>
                <div class="col-sm-9">
                    <p class="form-control-plaintext">{{ $historyBimbingan->tanggal_bimbingan->format('d F Y, H:i') }} WIB</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">Dosen Pembimbing:</label>
                <div class="col-sm-9">
                    <p class="form-control-plaintext">{{ $historyBimbingan->dosen && $historyBimbingan->dosen->user ? $historyBimbingan->dosen->user->name : 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">Topik Bimbingan:</label>
                <div class="col-sm-9">
                    <p class="form-control-plaintext">{{ $historyBimbingan->topik }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">Status Kehadiran:</label>
                <div class="col-sm-9">
                    <p class="form-control-plaintext">
                        <span class="badge
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
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">Catatan Awal Anda:</label>
                <div class="col-sm-9">
                    <div class="form-control-plaintext" style="white-space: pre-wrap;">{{ $historyBimbingan->catatan_mahasiswa }}</div>
                </div>
            </div>
            @endif

            @if($historyBimbingan->catatan_dosen)
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">Catatan dari Dosen:</label>
                <div class="col-sm-9">
                    <div class="form-control-plaintext" style="white-space: pre-wrap;">{{ $historyBimbingan->catatan_dosen }}</div>
                </div>
            </div>
            @endif

            @if($historyBimbingan->resume_bimbingan)
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">Resume Bimbingan:</label>
                <div class="col-sm-9">
                    <div class="form-control-plaintext" style="white-space: pre-wrap;">{{ $historyBimbingan->resume_bimbingan }}</div>
                </div>
            </div>
            @endif

            {{-- Jika ada file yang diupload (contoh) --}}
            @if($historyBimbingan->file_catatan_mahasiswa)
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">File Catatan Anda:</label>
                <div class="col-sm-9">
                    <p class="form-control-plaintext">
                        <a href="{{ Storage::url($historyBimbingan->file_catatan_mahasiswa) }}" target="_blank">Lihat/Unduh File</a>
                    </p>
                </div>
            </div>
            @endif

            @if($historyBimbingan->file_materi_dosen)
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label fw-bold">File Materi Dosen:</label>
                <div class="col-sm-9">
                    <p class="form-control-plaintext">
                        <a href="{{ Storage::url($historyBimbingan->file_materi_dosen) }}" target="_blank">Lihat/Unduh File</a>
                    </p>
                </div>
            </div>
            @endif

            {{-- Tombol Aksi untuk Mahasiswa (jika ada) --}}
            {{-- Contoh: Jika mahasiswa bisa membatalkan bimbingan yang masih terjadwal --}}
            @if($historyBimbingan->status_kehadiran == 'terjadwal')
                <hr>
                <h5>Aksi</h5>
                {{-- Jika Anda membuat form edit terpisah: --}}
                {{-- <a href="{{ route('mahasiswa.history-bimbingan.edit', $historyBimbingan->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Ajukan Pembatalan / Update Catatan
                </a> --}}

                {{-- Atau form pembatalan langsung di sini: --}}
                <form action="{{ route('mahasiswa.history-bimbingan.update', $historyBimbingan->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin membatalkan bimbingan ini?');">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="aksi_pembatalan" value="cancel">
                    {{-- Tambahkan input untuk alasan jika perlu --}}
                    {{-- <div class="mb-3">
                        <label for="alasan_pembatalan" class="form-label">Alasan Pembatalan (Opsional):</label>
                        <textarea name="alasan_pembatalan" id="alasan_pembatalan" class="form-control" rows="2"></textarea>
                    </div> --}}
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle"></i> Batalkan Bimbingan Ini
                    </button>
                </form>
            @endif
             {{-- Contoh: Jika mahasiswa bisa menambah catatan setelah bimbingan hadir/selesai --}}
            @if(in_array($historyBimbingan->status_kehadiran, ['hadir', 'selesai']))
                {{-- <hr>
                <h5>Update Catatan/File Anda</h5>
                <form action="{{ route('mahasiswa.history-bimbingan.update', $historyBimbingan->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="catatan_mahasiswa_update" class="form-label">Catatan Tambahan Anda:</label>
                        <textarea name="catatan_mahasiswa_update" id="catatan_mahasiswa_update" class="form-control" rows="3">{{ old('catatan_mahasiswa_update', $historyBimbingan->catatan_mahasiswa_update_field ?? '') }}</textarea>
                         @error('catatan_mahasiswa_update') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="file_catatan_mahasiswa" class="form-label">Upload File Catatan Baru (PDF, DOC, DOCX, maks 5MB):</label>
                        <input type="file" name="file_catatan_mahasiswa" id="file_catatan_mahasiswa" class="form-control">
                        @error('file_catatan_mahasiswa') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form> --}}
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Script JS jika diperlukan --}}
@endpush
