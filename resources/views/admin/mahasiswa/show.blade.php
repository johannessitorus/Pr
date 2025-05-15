@extends('layouts.app')

@section('title', 'Detail Mahasiswa')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Detail Mahasiswa</h1>
        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>

    @if(isset($mahasiswa))
        <div class="card shadow-sm">
            <div class="card-header">
                Informasi Mahasiswa
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">ID Mahasiswa</th>
                        <td>{{ $mahasiswa->id }}</td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td>{{ $mahasiswa->user->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>NIM</th>
                        <td>{{ $mahasiswa->nim ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $mahasiswa->user->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td>{{ $mahasiswa->user->username ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Program Studi</th>
                        <td>{{ $mahasiswa->prodi->nama_prodi ?? 'N/A' }} ({{ $mahasiswa->prodi->kode_prodi ?? 'N/A' }})</td>
                    </tr>
                    <tr>
                        <th>Angkatan</th>
                        <td>{{ $mahasiswa->angkatan ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Nomor Kelompok</th>
                        <td>{{ $mahasiswa->nomor_kelompok ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status Proyek Akhir</th>
                        <td>{{ Str::title(str_replace('_', ' ', $mahasiswa->status_proyek_akhir ?? 'Belum Ada')) }}</td>
                    </tr>
                    <tr>
                        <th>Dosen Pembimbing</th>
                        <td>{{ $mahasiswa->dosenPembimbing->user->name ?? 'Belum Ditentukan' }}</td>
                    </tr>
                    <tr>
                        <th>Judul Proyek Akhir</th>
                        <td>{{ $mahasiswa->judul_proyek_akhir ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Akun Dibuat Pada</th>
                        <td>{{ $mahasiswa->user->created_at ? $mahasiswa->user->created_at->format('d M Y, H:i') : '-' }}</td>
                    </tr>
                     <tr>
                        <th>Data Mahasiswa Dibuat Pada</th>
                        <td>{{ $mahasiswa->created_at ? $mahasiswa->created_at->format('d M Y, H:i') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Data mahasiswa tidak ditemukan.
        </div>
    @endif
</div>
@endsection
