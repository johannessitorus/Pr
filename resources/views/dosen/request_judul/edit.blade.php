@extends('layouts.app') {{-- Atau layout spesifik dosen --}}

@section('title', 'Proses Pengajuan Judul: ' . Str::limit($requestJudul->judul_diajukan, 30))

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Proses Pengajuan Judul</h1>
                <a href="{{ route('dosen.request-judul.show', $requestJudul->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Detail
                </a>
            </div>

            @include('partials.alerts') {{-- Untuk session error dari controller --}}

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Detail Pengajuan dari Mahasiswa</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Nama Mahasiswa</dt>
                        <dd class="col-sm-9">{{ $requestJudul->mahasiswa->user->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">NIM</dt>
                        <dd class="col-sm-9">{{ $requestJudul->mahasiswa->nim ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Program Studi</dt>
                        <dd class="col-sm-9">{{ $requestJudul->mahasiswa->prodi->nama_prodi ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Judul Diajukan</dt>
                        <dd class="col-sm-9">{{ $requestJudul->judul_diajukan }}</dd>

                        <dt class="col-sm-3">Deskripsi</dt>
                        <dd class="col-sm-9">{!! nl2br(e($requestJudul->deskripsi)) !!}</dd>

                        <dt class="col-sm-3">Tanggal Pengajuan</dt>
                        <dd class="col-sm-9">{{ $requestJudul->created_at->format('d M Y, H:i') }}</dd>

                        <dt class="col-sm-3">Status Saat Ini</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-warning text-dark">{{ ucfirst($requestJudul->status) }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Formulir Respons Dosen</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('dosen.request-judul.update', $requestJudul->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status" class="form-label">Tindakan (Status) <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="">-- Pilih Tindakan --</option>
                                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Setujui (Approved)</option>
                                <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Tolak (Rejected)</option>
                                {{-- Tambahkan status lain jika perlu, misal 'revisi_mahasiswa' --}}
                                {{-- <option value="revisi_mahasiswa" {{ old('status') == 'revisi_mahasiswa' ? 'selected' : '' }}>Minta Revisi</option> --}}
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="catatan_dosen" class="form-label">Catatan Dosen (Opsional)</label>
                            <textarea class="form-control @error('catatan_dosen') is-invalid @enderror" id="catatan_dosen" name="catatan_dosen" rows="5" placeholder="Berikan alasan atau masukan jika diperlukan...">{{ old('catatan_dosen') }}</textarea>
                            @error('catatan_dosen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('dosen.request-judul.show', $requestJudul->id) }}" class="btn btn-outline-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Respons
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
