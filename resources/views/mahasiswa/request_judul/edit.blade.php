@extends('layouts.app')

@section('title', 'Edit Pengajuan Judul')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Edit Pengajuan Judul</h1>
        <a href="{{ route('mahasiswa.request-judul.show', $requestJudul->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-times me-1"></i> Batal
        </a>
    </div>

    @include('partials.alerts')

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('mahasiswa.request-judul.update', $requestJudul->id) }}">
                @csrf
                @method('PUT') {{-- Penting untuk update --}}

                <div class="mb-3">
                    <label for="judul_diajukan" class="form-label">Judul yang Diajukan</label>
                    <input type="text" class="form-control @error('judul_diajukan') is-invalid @enderror" id="judul_diajukan" name="judul_diajukan" value="{{ old('judul_diajukan', $requestJudul->judul_diajukan) }}" required>
                    @error('judul_diajukan')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi Singkat</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="5" required>{{ old('deskripsi', $requestJudul->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="dosen_tujuan_id" class="form-label">Pilih Dosen Pembimbing</label>
                    <select class="form-select @error('dosen_tujuan_id') is-invalid @enderror" id="dosen_tujuan_id" name="dosen_tujuan_id" required>
                        <option value="">-- Pilih Dosen --</option>
                        @if(isset($calonDosenPembimbing) && $calonDosenPembimbing->count() > 0)
                            @foreach ($calonDosenPembimbing as $dosen)
                                @if($dosen->user)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_tujuan_id', $requestJudul->dosen_tujuan_id) == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->user->name }}
                                        @if($dosen->spesialisasi) ({{ Str::limit($dosen->spesialisasi, 30) }}) @endif
                                    </option>
                                @endif
                            @endforeach
                        @elseif ($requestJudul->dosenTujuan && $requestJudul->dosenTujuan->user)
                            {{-- Jika daftar calon kosong tapi sudah ada dosen terpilih sebelumnya (jarang terjadi jika logikanya benar) --}}
                             <option value="{{ $requestJudul->dosen_tujuan_id }}" selected>
                                {{ $requestJudul->dosenTujuan->user->name }}
                                @if($requestJudul->dosenTujuan->spesialisasi) ({{ Str::limit($requestJudul->dosenTujuan->spesialisasi, 30) }}) @endif
                            </option>
                            <option value="" disabled>Tidak ada dosen lain tersedia untuk prodi Anda</option>
                        @else
                             <option value="" disabled>Tidak ada dosen tersedia untuk prodi Anda</option>
                        @endif
                    </select>
                    @error('dosen_tujuan_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
