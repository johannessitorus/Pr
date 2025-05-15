@extends('layouts.app') {{-- Sesuaikan dengan layout admin Anda --}}

@section('title', 'Detail Dosen: ' . ($dosen->user->name ?? 'N/A'))

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Detail Dosen</h1>
    <p class="mb-4">Informasi lengkap mengenai dosen {{ $dosen->user->name ?? 'N/A' }}.</p>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Dosen</h6>
            <div>
                <a href="{{ route('admin.dosen.edit', $dosen->id) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit mr-1"></i> Edit Data
                </a>
                <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Data Akun Pengguna (User)</h5>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th width="40%">Nama Lengkap</th>
                            <td width="60%">: {{ $dosen->user->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td>: {{ $dosen->user->username ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>: {{ $dosen->user->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td>: <span class="badge badge-primary">{{ Str::ucfirst($dosen->user->role ?? 'N/A') }}</span></td>
                        </tr>
                        <tr>
                            <th>User Terdaftar Pada</th>
                            <td>: {{ $dosen->user ? $dosen->user->created_at->format('d M Y, H:i') : 'N/A' }}</td>
                        </tr>
                         <tr>
                            <th>User Diperbarui Pada</th>
                            <td>: {{ $dosen->user ? $dosen->user->updated_at->format('d M Y, H:i') : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Data Spesifik Dosen</h5>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th width="40%">NIDN/NIP</th>
                            <td width="60%">: {{ $dosen->nidn ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Program Studi</th>
                            <td>: {{ $dosen->prodi->nama_prodi ?? 'N/A' }}</td>
                        </tr>
                         <tr>
                            <th>Fakultas</th>
                            <td>: {{ $dosen->prodi->fakultas ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Spesialisasi</th>
                            <td>: {{ $dosen->spesialisasi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Data Dosen Dibuat Pada</th>
                            <td>: {{ $dosen->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Data Dosen Diperbarui Pada</th>
                            <td>: {{ $dosen->updated_at->format('d M Y, H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Anda bisa menambahkan bagian lain di sini, misalnya daftar mahasiswa bimbingan, dll. --}}
            {{-- Contoh:
            <hr>
            <h5>Mahasiswa Bimbingan Aktif</h5>
            @if($dosen->mahasiswaBimbingan && $dosen->mahasiswaBimbingan->count() > 0)
                <ul>
                    @foreach($dosen->mahasiswaBimbingan as $mhs)
                        <li>{{ $mhs->user->name }} ({{ $mhs->nim }})</li>
                    @endforeach
                </ul>
            @else
                <p>Tidak ada data mahasiswa bimbingan saat ini.</p>
            @endif
            --}}

        </div>
    </div>
</div>
@endsection
