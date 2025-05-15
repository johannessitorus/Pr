@extends('layouts.app') {{-- Atau layout spesifik dosen jika ada --}}

@section('title', 'Daftar Pengajuan Judul Mahasiswa')

@section('content')
<div class="container py-4 mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Pengajuan Judul dari Mahasiswa</h1>
        {{-- Tombol filter atau aksi lain bisa ditambahkan di sini --}}
    </div>

    @include('partials.alerts') {{-- Untuk menampilkan session success/error --}}

    {{-- Form Filter Status (Opsional) --}}
    <form method="GET" action="{{ route('dosen.request-judul.index') }}" class="mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="pending" {{ $filterStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $filterStatus == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $filterStatus == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="" {{ $filterStatus == '' ? 'selected' : '' }}>Semua Status</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </div>
    </form>


    <div class="card shadow-sm">
        <div class="card-body">
            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nama Mahasiswa</th>
                                <th scope="col">NIM</th>
                                <th scope="col">Prodi</th>
                                <th scope="col">Judul Diajukan</th>
                                <th scope="col">Tanggal Pengajuan</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $index => $request)
                                <tr>
                                    <th scope="row">{{ $requests->firstItem() + $index }}</th>
                                    <td>{{ $request->mahasiswa->user->name ?? 'N/A' }}</td>
                                    <td>{{ $request->mahasiswa->nim ?? 'N/A' }}</td>
                                    <td>{{ $request->mahasiswa->prodi->nama_prodi ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($request->judul_diajukan, 40) }}</td>
                                    <td>{{ $request->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <span class="badge
                                            @switch($request->status)
                                                @case('pending') bg-warning text-dark @break
                                                @case('approved') bg-success @break
                                                @case('rejected') bg-danger @break
                                                @case('cancelled_by_system') bg-secondary @break
                                                @default bg-secondary @break
                                            @endswitch
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('dosen.request-judul.show', $request->id) }}" class="btn btn-info btn-sm mb-1" title="Lihat Detail">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        @if($request->status == 'pending')
                                        <a href="{{ route('dosen.request-judul.edit', $request->id) }}" class="btn btn-warning btn-sm mb-1" title="Proses Pengajuan">
                                            <i class="fas fa-edit"></i> Proses
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $requests->appends(request()->query())->links() }} {{-- Penting untuk pagination dengan filter --}}
                </div>
            @else
                <div class="alert alert-info text-center">
                    Tidak ada pengajuan judul dengan filter saat ini.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
