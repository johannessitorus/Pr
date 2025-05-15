@extends('layouts.app')

@section('title', 'Edit Pengajuan Bimbingan')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Edit Pengajuan Bimbingan</h1>
                <a href="{{ route('mahasiswa.request-bimbingan.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>

            @include('partials.alerts')

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('mahasiswa.request-bimbingan.update', $requestBimbingan->id) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- Method untuk update --}}

                        <div class="mb-3">
                            <label class="form-label">Dosen Pembimbing</label>
                            <input type="text" class="form-control" value="{{ $dosenPembimbing->user->name ?? 'N/A' }}" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_usulan" class="form-label">Tanggal Usulan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal_usulan') is-invalid @enderror" id="tanggal_usulan" name="tanggal_usulan" value="{{ old('tanggal_usulan', $requestBimbingan->tanggal_usulan) }}" required min="{{ now()->toDateString() }}">
                                @error('tanggal_usulan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="jam_usulan" class="form-label">Jam Usulan <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('jam_usulan') is-invalid @enderror" id="jam_usulan" name="jam_usulan" value="{{ old('jam_usulan', $requestBimbingan->jam_usulan) }}" required>
                                @error('jam_usulan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="topik_bimbingan" class="form-label">Topik/Agenda Bimbingan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('topik_bimbingan') is-invalid @enderror" id="topik_bimbingan" name="topik_bimbingan" rows="4" required minlength="10">{{ old('topik_bimbingan', $requestBimbingan->topik_bimbingan) }}</textarea>
                            @error('topik_bimbingan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="lokasi_usulan" class="form-label">Lokasi Usulan (Opsional)</label>
                            <input type="text" class="form-control @error('lokasi_usulan') is-invalid @enderror" id="lokasi_usulan" name="lokasi_usulan" value="{{ old('lokasi_usulan', $requestBimbingan->lokasi_usulan) }}" placeholder="Contoh: Ruang Dosen, Online via Google Meet">
                            @error('lokasi_usulan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="catatan_mahasiswa" class="form-label">Catatan Tambahan (Opsional)</label>
                            <textarea class="form-control @error('catatan_mahasiswa') is-invalid @enderror" id="catatan_mahasiswa" name="catatan_mahasiswa" rows="3">{{ old('catatan_mahasiswa', $requestBimbingan->catatan_mahasiswa) }}</textarea>
                            @error('catatan_mahasiswa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('mahasiswa.request-bimbingan.show', $requestBimbingan->id) }}" class="btn btn-outline-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Set min date untuk tanggal_usulan
    document.addEventListener('DOMContentLoaded', function() {
        var today = new Date().toISOString().split('T')[0];
        var dateInput = document.getElementById('tanggal_usulan');
        if (dateInput) {
            // Jika tanggal usulan lama lebih awal dari hari ini, set min ke tanggal usulan lama
            // agar tidak error saat edit data lama. Jika tidak, set ke hari ini.
            var oldDate = dateInput.value;
            if (oldDate && oldDate < today) {
                // Tidak set min agar bisa melihat data lama, atau set ke oldDate jika ingin disabled
            } else {
                 dateInput.setAttribute('min', today);
            }
        }
    });
</script>
@endpush
