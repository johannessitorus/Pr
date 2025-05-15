@extends('layouts.app') {{-- Atau layout spesifik dosen jika ada --}}

@section('title', 'Detail Pengajuan Judul Mahasiswa')

@section('content')
<div class="container py-4 mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Detail Pengajuan Judul</h1>
        <a href="{{ route('dosen.request-judul.index', ['status' => session('last_filter_status', 'pending')]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    @include('partials.alerts') {{-- Untuk menampilkan session success/error --}}

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informasi Pengajuan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong class="d-block">Nama Mahasiswa:</strong>
                    <span>{{ $request->mahasiswa->user->name ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <strong class="d-block">NIM:</strong>
                    <span>{{ $request->mahasiswa->nim ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <strong class="d-block">Program Studi:</strong>
                    <span>{{ $request->mahasiswa->prodi->nama_prodi ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6 mb-3">
                    <strong class="d-block">Tanggal Pengajuan:</strong>
                    <span>{{ $requestJudul->created_at->format('d M Y, H:i') }}</span>
                </div>
                <div class="col-md-12 mb-3">
                    <strong class="d-block">Judul yang Diajukan:</strong>
                    <p class="lead">{{ $requestJudul->judul_diajukan }}</p>
                </div>
                <div class="col-md-12 mb-3">
                    <strong class="d-block">Deskripsi Singkat/Latar Belakang:</strong>
                    <p>{{ $requestJudul->deskripsi_judul ?: 'Tidak ada deskripsi tambahan.' }}</p>
                </div>

                @if($requestJudul->file_pendukung)
                <div class="col-md-12 mb-3">
                    <strong class="d-block">File Pendukung:</strong>
                    {{-- Asumsikan file_pendukung adalah path yang bisa diakses via storage link --}}
                    {{-- Jika menggunakan public disk, bisa langsung asset() --}}
                    <a href="{{ Storage::url($request->file_pendukung) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download"></i> Unduh File Pendukung
                    </a>
                    <small class="d-block text-muted">Nama file: {{ basename($request->file_pendukung) }}</small>
                </div>
                @endif

                <div class="col-md-12 mb-3">
                    <strong class="d-block">Status Saat Ini:</strong>
                    <span class="badge fs-6
                        @switch($requestJudul->status)
                            @case('pending') bg-warning text-dark @break
                            @case('approved') bg-success @break
                            @case('rejected') bg-danger @break
                            @case('cancelled_by_system') bg-secondary @break
                            @default bg-secondary @break
                        @endswitch
                    ">
                        {{ ucfirst(str_replace('_', ' ', $requestJudul->status)) }}
                    </span>
                </div>

                @if($requestJudul->status == 'approved' || $requestJudul->status == 'rejected')
                    <div class="col-md-12 mb-3">
                        <strong class="d-block">Tanggal Diproses:</strong>
                        <span>{{ $requestJudul->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="col-md-12 mb-3">
                        <strong class="d-block">Catatan Dosen:</strong>
                        <p class="fst-italic">{{ $requestJudul->catatan_dosen ?: 'Tidak ada catatan.' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($requestJudul->status == 'pending')
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Proses Pengajuan</h5>
        </div>
        <div class="card-body">
            <p>Silakan berikan keputusan untuk pengajuan judul ini.</p>
            <div class="row">
                {{-- Form Approve --}}
                <div class="col-md-6 border-end">
                    <h6 class="text-success">Setujui Pengajuan</h6>
                    <form method="POST" action="{{ route('dosen.request-judul.approve', $request->id) }}" id="form-approve">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="catatan_dosen_approve" class="form-label">Catatan Tambahan (Opsional):</label>
                            <textarea name="catatan_dosen" id="catatan_dosen_approve" class="form-control" rows="3" placeholder="Mis: Judul menarik, silakan lanjutkan ke tahap berikutnya."></textarea>
                            @error('catatan_dosen', 'approve') {{-- Error bag for specific form --}}
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Setujui Judul
                        </button>
                    </form>
                </div>

                {{-- Form Reject --}}
                <div class="col-md-6">
                    <h6 class="text-danger">Tolak Pengajuan</h6>
                    <form method="POST" action="{{ route('dosen.request-judul.reject', $request->id) }}" id="form-reject">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="catatan_dosen_reject" class="form-label">Alasan Penolakan <span class="text-danger">*</span>:</label>
                            <textarea name="catatan_dosen" id="catatan_dosen_reject" class="form-control" rows="3" placeholder="Mis: Judul sudah pernah ada, topik kurang relevan, metodologi tidak sesuai." required></textarea>
                            @error('catatan_dosen', 'reject') {{-- Error bag for specific form --}}
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Tolak Judul
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formApprove = document.getElementById('form-approve');
    const formReject = document.getElementById('form-reject');

    if(formApprove) {
        formApprove.addEventListener('submit', function(e) {
            if (!confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan judul ini?')) {
                e.preventDefault();
            }
        });
    }

    if(formReject) {
        formReject.addEventListener('submit', function(e) {
            const alasan = document.getElementById('catatan_dosen_reject').value.trim();
            if (alasan === "") {
                alert('Alasan penolakan tidak boleh kosong.');
                e.preventDefault();
                return;
            }
            if (!confirm('Apakah Anda yakin ingin MENOLAK pengajuan judul ini?')) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endpush
