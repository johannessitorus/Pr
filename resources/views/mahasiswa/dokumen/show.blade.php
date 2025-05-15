@extends('layouts.app') {{-- Sesuaikan dengan layout mahasiswa Anda --}}

@section('title', 'Detail Dokumen Proyek Akhir')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="mb-0 h3">Detail Dokumen: {{ $dokumenProyekAkhir->jenisDokumen->nama_jenis ?? 'N/A' }}</h1>
                <div>
                    @if(in_array($dokumenProyekAkhir->status_review, ['revision_needed']))
                        {{-- Tombol untuk halaman revisi jika ada --}}
                        {{-- <a href="{{ route('mahasiswa.dokumen-proyek-akhir.edit', $dokumenProyekAkhir->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-pencil-alt me-1"></i> Revisi Dokumen
                        </a> --}}
                    @endif
                    <a href="{{ route('mahasiswa.dokumen.index') }}" class="btn btn-outline-secondary btn-sm ms-2">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Dokumen
                    </a>
                </div>
            </div>

            @include('partials.alerts')

            <div class="card shadow-sm">
                <div class="card-header">
                    Informasi Dokumen (Versi {{ $dokumenProyekAkhir->versi }})
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Jenis Dokumen</dt>
                                <dd class="col-sm-7">{{ $dokumenProyekAkhir->jenisDokumen->nama_jenis ?? 'N/A' }}</dd>

                                <dt class="col-sm-5">Nama File Asli</dt>
                                <dd class="col-sm-7">
                                    <a href="{{ Storage::url($dokumenProyekAkhir->file_path) }}" target="_blank" title="Lihat/Unduh">
                                        {{ $dokumenProyekAkhir->nama_file_asli }}
                                    </a>
                                </dd>

                                <dt class="col-sm-5">Nama File Tersimpan</dt>
                                <dd class="col-sm-7">{{ $dokumenProyekAkhir->nama_file_unik }}</dd>

                                <dt class="col-sm-5">Ekstensi</dt>
                                <dd class="col-sm-7">{{ strtoupper($dokumenProyekAkhir->ekstensi_file) }}</dd>

                                <dt class="col-sm-5">Ukuran File</dt>
                                <dd class="col-sm-7">{{ number_format($dokumenProyekAkhir->ukuran_file / 1024, 2) }} KB</dd>

                                <dt class="col-sm-5">Versi</dt>
                                <dd class="col-sm-7">{{ $dokumenProyekAkhir->versi }}</dd>

                                <dt class="col-sm-5">Tanggal Submit</dt>
                                <dd class="col-sm-7">{{ $dokumenProyekAkhir->created_at->format('d M Y, H:i:s') }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                             <dl class="row">
                                <dt class="col-sm-5">Status Review</dt>
                                <dd class="col-sm-7">
                                    @php
                                        $statusClass = '';
                                        switch ($dokumenProyekAkhir->status_review) {
                                            case 'pending': $statusClass = 'text-warning'; break;
                                            case 'approved': $statusClass = 'text-success'; break;
                                            case 'revision_needed': $statusClass = 'text-danger'; break;
                                            case 'rejected': $statusClass = 'text-secondary'; break;
                                        }
                                    @endphp
                                    <strong class="{{ $statusClass }}">
                                        {{ Str::title(str_replace('_', ' ', $dokumenProyekAkhir->status_review)) }}
                                    </strong>
                                </dd>

                                <dt class="col-sm-5">Direview Oleh</dt>
                                <dd class="col-sm-7">{{ $dokumenProyekAkhir->reviewer->name ?? '-' }}</dd>

                                <dt class="col-sm-5">Tanggal Review</dt>
                                <dd class="col-sm-7">{{ $dokumenProyekAkhir->reviewed_at ? $dokumenProyekAkhir->reviewed_at->format('d M Y, H:i:s') : '-' }}</dd>

                                <dt class="col-sm-12">Catatan Mahasiswa:</dt>
                                <dd class="col-sm-12">
                                    <div class="p-2 bg-light border rounded" style="min-height: 60px;">
                                        {!! nl2br(e($dokumenProyekAkhir->catatan_mahasiswa ?? 'Tidak ada catatan.')) !!}
                                    </div>
                                </dd>

                                <dt class="col-sm-12 mt-2">Catatan Reviewer:</dt>
                                <dd class="col-sm-12">
                                    <div class="p-2 bg-light border rounded" style="min-height: 60px;">
                                        {!! nl2br(e($dokumenProyekAkhir->catatan_reviewer ?? 'Belum ada catatan dari reviewer.')) !!}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
