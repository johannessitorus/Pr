@extends('layouts.app')

@section('title', 'Ajukan Judul Proyek Akhir Baru')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Formulir Pengajuan Judul Baru</h1>
                <a href="{{ route('mahasiswa.request-judul.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>

            @include('partials.alerts') {{-- Untuk menampilkan session error dari controller --}}

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('mahasiswa.request-judul.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="judul_diajukan" class="form-label">Judul yang Diajukan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('judul_diajukan') is-invalid @enderror" id="judul_diajukan" name="judul_diajukan" value="{{ old('judul_diajukan') }}" required minlength="10" maxlength="255">
                            @error('judul_diajukan')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">Minimal 10 karakter, maksimal 255 karakter.</small>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi Singkat Judul <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="5" required minlength="20">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">Jelaskan secara singkat mengenai latar belakang dan tujuan dari judul yang Anda ajukan. Minimal 20 karakter.</small>
                        </div>

                        <div class="mb-3">
                            <label for="dosen_tujuan_id" class="form-label">Pilih Dosen Tujuan (Calon Pembimbing) <span class="text-danger">*</span></label>
                            <select class="form-select @error('dosen_tujuan_id') is-invalid @enderror" id="dosen_tujuan_id" name="dosen_tujuan_id" required>
                                <option value="" disabled {{ old('dosen_tujuan_id') ? '' : 'selected' }}>-- Pilih Dosen --</option>
                                @if(isset($calonDosenPembimbing) && $calonDosenPembimbing->count() > 0)
                                    @foreach ($calonDosenPembimbing as $dosen)
                                        <option value="{{ $dosen->id }}" {{ old('dosen_tujuan_id') == $dosen->id ? 'selected' : '' }}>
                                            {{ $dosen->user->name ?? 'Nama Dosen Tidak Tersedia' }} (NIDN: {{ $dosen->nidn }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>Tidak ada dosen yang tersedia untuk program studi Anda saat ini.</option>
                                @endif
                            </select>
                            @error('dosen_tujuan_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">Pilih salah satu dosen dari program studi Anda sebagai calon pembimbing.</small>
                        </div>

                        <hr>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('mahasiswa.request-judul.index') }}" class="btn btn-outline-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Kirim Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- Jika ada style khusus untuk halaman ini --}}
<style>
    .form-label {
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
{{-- Jika ada script khusus untuk halaman ini, misalnya untuk editor WYSIWYG pada deskripsi --}}
<script>
    // Contoh: Inisialisasi editor jika ada
    // ClassicEditor
    //     .create( document.querySelector( '#deskripsi' ) )
    //     .catch( error => {
    //         console.error( error );
    //     } );
</script>
@endpush
