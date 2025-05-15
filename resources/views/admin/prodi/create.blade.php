{{-- Misal Anda punya layout admin: resources/views/layouts/admin.blade.php --}}
@extends('layouts.app') {{-- Sesuaikan dengan nama layout Anda --}}

@section('content')
<div class="container mt-5">
    <h1>Tambah Prodi Baru</h1>

    {{-- Menampilkan pesan error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.prodi.store') }}" method="POST">
        @csrf {{-- Token CSRF untuk keamanan --}}

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="kode_prodi" class="form-label">Kode Prodi</label>
                    <input type="text" class="form-control @error('kode_prodi') is-invalid @enderror" id="kode_prodi" name="kode_prodi" value="{{ old('kode_prodi') }}" required>
                    @error('kode_prodi')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="nama_prodi" class="form-label">Nama Prodi</label>
                    <input type="text" class="form-control @error('nama_prodi') is-invalid @enderror" id="nama_prodi" name="nama_prodi" value="{{ old('nama_prodi') }}" required>
                    @error('nama_prodi')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="fakultas" class="form-label">Fakultas</label>
                    <input type="text" class="form-control @error('fakultas') is-invalid @enderror" id="fakultas" name="fakultas" value="{{ old('fakultas') }}"> {{-- required jika memang wajib --}}
                    @error('fakultas')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                    <small class="form-text text-muted">Opsional, bisa dikosongkan jika tidak ada.</small> {{-- Tambahkan petunjuk jika opsional --}}
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.prodi.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
