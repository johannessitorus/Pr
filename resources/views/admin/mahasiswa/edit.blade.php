@extends('layouts.app')

@section('title', 'Edit Data Mahasiswa')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="mb-0">Edit Data Mahasiswa: {{ $mahasiswa->user->name ?? $mahasiswa->nim }}</h1>
                <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-secondary">Kembali ke Daftar Mahasiswa</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    {{-- Pastikan $mahasiswa dan $mahasiswa->user ada --}}
                    @if(isset($mahasiswa) && isset($mahasiswa->user))
                        <form action="{{ route('admin.mahasiswa.update', $mahasiswa->id) }}" method="POST">
                            @csrf
                            @method('PUT') {{-- Method spoofing untuk rute update --}}

                            {{-- Data Akun Pengguna --}}
                            <h5 class="mb-3">Data Akun Pengguna</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $mahasiswa->user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $mahasiswa->user->username) }}" required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $mahasiswa->user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password Baru (Opsional)</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Isi jika ingin mengganti password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- Data Mahasiswa --}}
                            <h5 class="mb-3">Data Detail Mahasiswa</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nim" class="form-label">NIM (Nomor Induk Mahasiswa) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nim') is-invalid @enderror" id="nim" name="nim" value="{{ old('nim', $mahasiswa->nim) }}" required>
                                    @error('nim')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="prodi_id" class="form-label">Program Studi <span class="text-danger">*</span></label>
                                    <select class="form-select @error('prodi_id') is-invalid @enderror" id="prodi_id" name="prodi_id" required>
                                        <option value="">Pilih Program Studi</option>
                                        @foreach($prodis as $prodi)
                                            <option value="{{ $prodi->id }}" {{ old('prodi_id', $mahasiswa->prodi_id) == $prodi->id ? 'selected' : '' }}>
                                                {{ $prodi->nama_prodi }} ({{ $prodi->kode_prodi }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('prodi_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="angkatan" class="form-label">Angkatan (Tahun Masuk) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('angkatan') is-invalid @enderror" id="angkatan" name="angkatan" value="{{ old('angkatan', $mahasiswa->angkatan) }}" placeholder="Contoh: 2020" min="1900" max="{{ date('Y') + 5 }}" required>
                                    @error('angkatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nomor_kelompok" class="form-label">Nomor Kelompok (Jika Ada)</label>
                                    <input type="text" class="form-control @error('nomor_kelompok') is-invalid @enderror" id="nomor_kelompok" name="nomor_kelompok" value="{{ old('nomor_kelompok', $mahasiswa->nomor_kelompok) }}">
                                    @error('nomor_kelompok')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                             <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status_proyek_akhir" class="form-label">Status Proyek Akhir <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status_proyek_akhir') is-invalid @enderror" id="status_proyek_akhir" name="status_proyek_akhir" required>
                                        @php
                                            $statuses = ['belum_ada', 'pengajuan_judul', 'bimbingan', 'selesai', 'revisi'];
                                        @endphp
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ old('status_proyek_akhir', $mahasiswa->status_proyek_akhir) == $status ? 'selected' : '' }}>
                                                {{ Str::title(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status_proyek_akhir')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Anda bisa menambahkan field lain jika diperlukan, misalnya Dosen Pembimbing jika sudah ada --}}
                            </div>


                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-link">Batal</a>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-danger">
                            Data mahasiswa atau data pengguna terkait tidak ditemukan.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Jika ada script khusus untuk halaman edit --}}
@endpush
