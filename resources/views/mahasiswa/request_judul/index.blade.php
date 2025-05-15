@extends('layouts.app')

@section('title', 'Daftar Pengajuan Judul')

@section('content')
<div class="container py-4 mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Daftar Pengajuan Judul Anda</h1>
        <a href="{{ route('mahasiswa.request-judul.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Ajukan Judul Baru
        </a>
    </div>

    @include('partials.alerts') {{-- Untuk menampilkan session success/error --}}

    <div class="card shadow-sm">
        <div class="card-body">
            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">No.</th>
                                <th scope="col">Judul Diajukan</th>
                                <th scope="col">Dosen Tujuan</th>
                                <th scope="col">Tanggal Pengajuan</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $index => $request)
                                <tr>
                                    <th scope="row">{{ $requests->firstItem() + $index }}</th>
                                    <td>{{ Str::limit($request->judul_diajukan, 50) }}</td>
                                    <td>{{ $request->dosenTujuan->user->name ?? 'N/A' }}</td>
                                    <td>{{ $request->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <span class="badge
                                            @switch($request->status)
                                                @case('pending') bg-warning text-dark @break
                                                @case('approved') bg-success @break
                                                @case('rejected') bg-danger @break
                                                @default bg-secondary @break
                                            @endswitch
                                        ">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('mahasiswa.request-judul.show', $request->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {{-- Tambahkan tombol edit/delete jika diizinkan dan statusnya 'pending' --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    Anda belum pernah mengajukan judul.
                    <a href="{{ route('mahasiswa.request-judul.create') }}" class="btn btn-link">Ajukan sekarang?</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
