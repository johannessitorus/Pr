@extends('layouts.app') {{-- Sesuaikan dengan layout mahasiswa Anda --}}

@section('title', 'Revisi Dokumen Proyek Akhir')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="mb-0 h3">Revisi Dokumen: {{ $dokumenProyekAkhir->jenisDokumen->nama_jenis ?? 'N/A' }} (Versi {{ $dokumenProyekAkhir->versi }})</h1>
                <a href="{{ route('mahasiswa.dokumen.show', $dokumenProyekAkhir->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Detail Dokumen
                </a>
            </div>

            @include('partials.alerts')

            <div class="card shadow-sm mb-3">
                <div class="card-header">Informasi Dokumen Saat Ini</div>
                <div class="card-body">
                    <p><strong>Nama File Asli:</strong> {{ $dokumenProyekAkhir->nama_file_asli }}</p>
                    <p><strong>Catatan Reviewer:</strong></p>
                    <div class="p-2 bg-light border rounded">
                        {!! nl2br(e($dokumenProyekAkhir->catatan_reviewer ?? 'Tidak ada catatan dari reviewer.')) !!}
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                 <div class="card-header">Form Upload Revisi</div>
                <div class="card-body">
                    {{-- Anda bisa menggunakan action yang sama (store) jika controller store menangani update versi,
                         atau buat method update khusus untuk revisi. Di sini saya asumsikan bisa pakai 'update' --}}
                    <form action="{{ route('mahasiswa.dokumen.update', $dokumenProyekAkhir->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Jenis dokumen biasanya tidak diubah saat revisi, tapi bisa ditampilkan sebagai info --}}
                        <div class="mb-3">
                            <label class="form-label">Jenis Dokumen</label>
                            <input type="text" class="form-control" value="{{ $dokumenProyekAkhir->jenisDokumen->nama_jenis ?? 'N/A' }}" readonly>
                            {{-- Hidden input untuk jenis_dokumen_id jika diperlukan oleh controller update --}}
                            <input type="hidden" name="jenis_dokumen_id" value="{{ $dokumenProyekAkhir->jenis_dokumen_id }}">
                        </div>

                        <div class="mb-3">
                            <label for="file_dokumen" class="form-label">Upload File Revisi <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file_dokumen') is-invalid @enderror" id="file_dokumen" name="file_dokumen" required>
                            <small class="form-text text-muted">Upload file baru untuk menggantikan versi sebelumnya. Format: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR. Maks: 10MB.</small>
                            @error('file_dokumen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="catatan_mahasiswa" class="form-label">Catatan Revisi (Opsional)</label>
                            <textarea class="form-control @error('catatan_mahasiswa') is-invalid @enderror" id="catatan_mahasiswa" name="catatan_mahasiswa" rows="3">{{ old('catatan_mahasiswa', $dokumenProyekAkhir->catatan_mahasiswa) }}</textarea>
                             <small class="form-text text-muted">Berikan catatan singkat mengenai revisi yang Anda lakukan.</small>
                            @error('catatan_mahasiswa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <a href="{{ route('mahasiswa.dokumen.show', $dokumenProyekAkhir->id) }}" class="btn btn-link me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Submit Revisi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
