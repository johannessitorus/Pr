@extends('layouts.app')

@section('title', 'Edit Jenis Dokumen')

@section('content')
<div class="container py-4">
     <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="mb-0 h3">Edit Jenis Dokumen: {{ $jenisDokuman->nama_jenis }}</h1>
                <a href="{{ route('admin.jenis-dokumen.index') }}" class="btn btn-outline-secondary btn-sm">
                     <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.jenis-dokumen.update', $jenisDokuman->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nama_jenis" class="form-label">Nama Jenis Dokumen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_jenis') is-invalid @enderror" id="nama_jenis" name="nama_jenis" value="{{ old('nama_jenis', $jenisDokuman->nama_jenis) }}" required autofocus>
                            @error('nama_jenis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $jenisDokuman->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                             <a href="{{ route('admin.jenis-dokumen.index') }}" class="btn btn-link me-2">Batal</a>
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
