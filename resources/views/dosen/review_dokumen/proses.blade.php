@extends('layouts.app') {{-- Atau layout dosen --}}

@section('title', 'Proses Review Dokumen')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="mb-0 h3">Proses Review Dokumen</h1>
                <a href="{{ route('dosen.review-dokumen.index') }}" class="btn btn-outline-secondary btn-sm">
                     <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Review
                </a>
            </div>

            @include('partials.alerts')

            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    Detail Dokumen: {{ $dokumenProyekAkhir->jenisDokumen->nama_jenis ?? 'N/A' }} - Versi {{ $dokumenProyekAkhir->versi }}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Mahasiswa:</strong> {{ $dokumenProyekAkhir->mahasiswa->user->name ?? 'N/A' }} ({{ $dokumenProyekAkhir->mahasiswa->nim ?? 'N/A' }})</p>
                            <p><strong>Jenis Dokumen:</strong> {{ $dokumenProyekAkhir->jenisDokumen->nama_jenis ?? 'N/A' }}</p>
                            <p><strong>Nama File:</strong>
                                <a href="{{ Storage::url($dokumenProyekAkhir->file_path) }}" target="_blank" title="Lihat/Unduh {{ $dokumenProyekAkhir->nama_file_asli }}">
                                    {{ $dokumenProyekAkhir->nama_file_asli }}
                                </a>
                            </p>
                            <p><strong>Versi:</strong> {{ $dokumenProyekAkhir->versi }}</p>
                            <p><strong>Tanggal Submit/Update:</strong> {{ $dokumenProyekAkhir->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status Saat Ini:</strong>
                                <span class="fw-bold">{{ Str::title(str_replace('_', ' ', $dokumenProyekAkhir->status_review)) }}</span>
                            </p>
                            <p><strong>Catatan dari Mahasiswa:</strong></p>
                            <div class="p-2 bg-light border rounded" style="min-height: 70px;">
                                {!! nl2br(e($dokumenProyekAkhir->catatan_mahasiswa ?? 'Tidak ada catatan.')) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">Form Review Dokumen</div>
                <div class="card-body">
                    <form action="{{ route('dosen.review-dokumen.update', $dokumenProyekAkhir->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status_review" class="form-label">Status Review <span class="text-danger">*</span></label>
                            <select class="form-select @error('status_review') is-invalid @enderror" id="status_review" name="status_review" required>
                                <option value="">Pilih Status...</option>
                                <option value="approved" {{ old('status_review', $dokumenProyekAkhir->status_review) == 'approved' ? 'selected' : '' }}>Approved (Disetujui)</option>
                                <option value="revision_needed" {{ old('status_review', $dokumenProyekAkhir->status_review) == 'revision_needed' ? 'selected' : '' }}>Revision Needed (Perlu Revisi)</option>
                                <option value="rejected" {{ old('status_review', $dokumenProyekAkhir->status_review) == 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                            </select>
                            @error('status_review')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="catatan_reviewer" class="form-label">Catatan Reviewer (Opsional)</label>
                            <textarea class="form-control @error('catatan_reviewer') is-invalid @enderror" id="catatan_reviewer" name="catatan_reviewer" rows="5">{{ old('catatan_reviewer', $dokumenProyekAkhir->catatan_reviewer) }}</textarea>
                            <small class="form-text text-muted">Berikan feedback, alasan penolakan, atau poin revisi untuk mahasiswa.</small>
                            @error('catatan_reviewer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <a href="{{ route('dosen.review-dokumen.index') }}" class="btn btn-link me-2">Batal</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-circle me-1"></i> Simpan Review
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
