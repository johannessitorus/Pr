@extends('layouts.app') {{-- Atau layout spesifik dosen --}}

@section('title', 'Daftar Request Bimbingan Mahasiswa')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Request Bimbingan dari Mahasiswa</h1>
        {{-- Tombol filter atau aksi lain bisa ditambahkan di sini --}}
    </div>

    @include('partials.alerts')

    {{-- Form Filter Status (Opsional) --}}
    <form method="GET" action="{{ route('dosen.request-bimbingan.index') }}" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-3">
                <label for="status_filter" class="visually-hidden">Filter Status</label>
                <select name="status" id="status_filter" class="form-select form-select-sm">
                    <option value="pending" {{ ($filterStatus ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ ($filterStatus ?? '') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ ($filterStatus ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="rescheduled" {{ ($filterStatus ?? '') == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                    <option value="completed" {{ ($filterStatus ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="" {{ ($filterStatus ?? '') == '' ? 'selected' : '' }}>Semua Status</option>
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
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nama Mahasiswa</th>
                                <th scope="col">NIM</th>
                                <th scope="col">Tgl & Jam Usulan Mhs</th>
                                <th scope="col">Topik</th>
                                <th scope="col">Status</th>
                                <th scope="col">Diajukan Pada</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $index => $request)
                                <tr>
                                    <th scope="row">{{ $requests->firstItem() + $index }}</th>
                                    <td>{{ $request->mahasiswa->user->name ?? 'N/A' }}</td>
                                    <td>{{ $request->mahasiswa->nim ?? 'N/A' }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($request->tanggal_usulan)->format('d M Y') }}<br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($request->jam_usulan)->format('H:i') }}</small>
                                    </td>
                                    <td>{{ Str::limit($request->topik_bimbingan, 40) }}</td>
                                    <td>
                                        <span class="badge
                                            @switch($request->status_request)
                                                @case('pending') bg-warning text-dark @break
                                                @case('approved') bg-success @break
                                                @case('rejected') bg-danger @break
                                                @case('rescheduled') bg-info text-dark @break
                                                @case('completed') bg-primary @break
                                                @case('cancelled') bg-secondary @break
                                                @default bg-light text-dark @break
                                            @endswitch
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $request->status_request)) }}
                                        </span>
                                         @if($request->status_request == 'rescheduled' && $request->tanggal_dosen)
                                            <br><small class="text-info">Anda Reschedule: {{ \Carbon\Carbon::parse($request->tanggal_dosen)->format('d M Y') }}, {{ \Carbon\Carbon::parse($request->jam_dosen)->format('H:i') }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $request->created_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('dosen.request-bimbingan.show', $request->id) }}" class="btn btn-info btn-sm my-1" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($request->status_request == 'pending')
                                        <a href="{{ route('dosen.request-bimbingan.edit', $request->id) }}" class="btn btn-warning btn-sm my-1" title="Proses Pengajuan">
                                            <i class="fas fa-edit"></i>
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
                    Tidak ada request bimbingan dengan filter saat ini.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
