@extends('layouts.app') {{-- Sesuaikan dengan layout mahasiswa Anda --}}

@section('title', 'Submit Dokumen Proyek Akhir')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="mb-0 h3">Submit Dokumen Proyek Akhir</h1>
                <a href="{{ route('mahasiswa.dokumen.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Dokumen
                </a>
            </div>

            @include('partials.alerts') {{-- Atau logika alert langsung --}}

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('mahasiswa.dokumen.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="jenis_dokumen_id" class="form-label">Jenis Dokumen <span class="text-danger">*</span></label>
                            <select class="form-select @error('jenis_dokumen_id') is-invalid @enderror" id="jenis_dokumen_id" name="jenis_dokumen_id" required>
                                <option value="">Pilih Jenis Dokumen...</option>
                                @foreach($jenisDokumens as $jenis)
                                    <option value="{{ $jenis->id }}" {{ old('jenis_dokumen_id') == $jenis->id ? 'selected' : '' }}>
                                        {{ $jenis->nama_jenis }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_dokumen_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="file_dokumen" class="form-label">Upload File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file_dokumen') is-invalid @enderror" id="file_dokumen" name="file_dokumen" required>
                            <small class="form-text text-muted">Format yang diizinkan: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR. Maksimal ukuran: 10MB.</small>
                            @error('file_dokumen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="catatan_mahasiswa" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control @error('catatan_mahasiswa') is-invalid @enderror" id="catatan_mahasiswa" name="catatan_mahasiswa" rows="3">{{ old('catatan_mahasiswa') }}</textarea>
                            @error('catatan_mahasiswa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                             <a href="{{ route('mahasiswa.dokumen.index') }}" class="btn btn-link me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Submit Dokumen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
