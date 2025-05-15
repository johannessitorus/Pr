@extends('layouts.app') {{-- Atau layout spesifik dosen --}}

@section('title', 'Proses Request Bimbingan')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Proses Request Bimbingan</h1>
                <a href="{{ route('dosen.request-bimbingan.show', $requestBimbingan->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Detail
                </a>
            </div>

            @include('partials.alerts')

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Detail Request dari: {{ $requestBimbingan->mahasiswa->user->name ?? 'N/A' }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>Judul/Topik Usulan:</strong> {{ $requestBimbingan->topik_bimbingan }}</p>
                    <p><strong>Tanggal & Jam Usulan Mahasiswa:</strong> {{ \Carbon\Carbon::parse($requestBimbingan->tanggal_usulan)->format('d F Y') }} pukul {{ \Carbon\Carbon::parse($requestBimbingan->jam_usulan)->format('H:i') }}</p>
                    @if($requestBimbingan->lokasi_usulan)
                        <p><strong>Lokasi Usulan:</strong> {{ $requestBimbingan->lokasi_usulan }}</p>
                    @endif
                    @if($requestBimbingan->catatan_mahasiswa)
                        <p><strong>Catatan Mahasiswa:</strong> {!! nl2br(e($requestBimbingan->catatan_mahasiswa)) !!}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Formulir Respons Anda</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('dosen.request-bimbingan.update', $requestBimbingan->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status_request" class="form-label">Tindakan (Status) <span class="text-danger">*</span></label>
                            <select class="form-select @error('status_request') is-invalid @enderror" id="status_request" name="status_request" required>
                                <option value="">-- Pilih Tindakan --</option>
                                <option value="approved" {{ old('status_request', $requestBimbingan->status_request) == 'approved' ? 'selected' : '' }}>Setujui Usulan Mahasiswa</option>
                                <option value="rejected" {{ old('status_request') == 'rejected' ? 'selected' : '' }}>Tolak</option>
                                <option value="rescheduled" {{ old('status_request') == 'rescheduled' ? 'selected' : '' }}>Setujui dengan Jadwal Berbeda (Reschedule)</option>
                            </select>
                            @error('status_request')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kolom untuk reschedule, hanya muncul jika status 'rescheduled' dipilih --}}
                        <div id="reschedule_fields" class="row mb-3" style="display: {{ old('status_request', $requestBimbingan->status_request) == 'rescheduled' ? 'flex' : 'none' }};">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="tanggal_dosen" class="form-label">Tanggal Pengganti</label>
                                <input type="date" class="form-control @error('tanggal_dosen') is-invalid @enderror" id="tanggal_dosen" name="tanggal_dosen" value="{{ old('tanggal_dosen', $requestBimbingan->tanggal_dosen) }}" min="{{ now()->toDateString() }}">
                                @error('tanggal_dosen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="jam_dosen" class="form-label">Jam Pengganti</label>
                                <input type="time" class="form-control @error('jam_dosen') is-invalid @enderror" id="jam_dosen" name="jam_dosen" value="{{ old('jam_dosen', $requestBimbingan->jam_dosen) }}">
                                @error('jam_dosen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="catatan_dosen" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control @error('catatan_dosen') is-invalid @enderror" id="catatan_dosen" name="catatan_dosen" rows="5" placeholder="Berikan alasan, masukan, atau informasi tambahan jika diperlukan...">{{ old('catatan_dosen', $requestBimbingan->catatan_dosen) }}</textarea>
                            @error('catatan_dosen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('dosen.request-bimbingan.show', $requestBimbingan->id) }}" class="btn btn-outline-secondary me-2">Batal</a>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusSelect = document.getElementById('status_request');
        const rescheduleFields = document.getElementById('reschedule_fields');
        const tanggalDosenInput = document.getElementById('tanggal_dosen');
        const jamDosenInput = document.getElementById('jam_dosen');

        function toggleRescheduleFields() {
            if (statusSelect.value === 'rescheduled') {
                rescheduleFields.style.display = 'flex';
                tanggalDosenInput.required = true;
                jamDosenInput.required = true;
            } else {
                rescheduleFields.style.display = 'none';
                tanggalDosenInput.required = false;
                jamDosenInput.required = false;
                // Kosongkan nilai jika tidak reschedule untuk menghindari validasi yang tidak perlu
                // tanggalDosenInput.value = '';
                // jamDosenInput.value = '';
            }
        }

        if(statusSelect) {
            statusSelect.addEventListener('change', toggleRescheduleFields);
            // Panggil sekali saat load untuk menangani old input atau data dari database
            toggleRescheduleFields();
        }

        var today = new Date().toISOString().split('T')[0];
        if (tanggalDosenInput) {
             var oldDate = tanggalDosenInput.value;
            if (oldDate && oldDate < today) {
                // biarkan untuk edit data lama
            } else {
                 tanggalDosenInput.setAttribute('min', today);
            }
        }
    });
</script>
@endpush
