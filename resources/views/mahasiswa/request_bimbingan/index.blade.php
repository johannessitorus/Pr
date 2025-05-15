@extends('layouts.app')

@section('title', 'Daftar Pengajuan Bimbingan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Daftar Pengajuan Bimbingan Anda</h1>
        {{-- Tombol Ajukan Baru hanya muncul jika mahasiswa punya dosen pembimbing --}}
        @if(Auth::user()->mahasiswa && Auth::user()->mahasiswa->dosen_pembimbing_id)
            <a href="{{ route('mahasiswa.request-bimbingan.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Ajukan Bimbingan Baru
            </a>
        @endif
    </div>

    @include('partials.alerts')

    @if(Auth::user()->mahasiswa && !Auth::user()->mahasiswa->dosen_pembimbing_id)
        <div class="alert alert-warning">
            Anda belum memiliki dosen pembimbing yang ditetapkan. Anda baru bisa mengajukan bimbingan setelah dosen pembimbing Anda ditentukan (biasanya setelah judul proyek akhir Anda disetujui).
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Dosen Pembimbing</th>
                                <th scope="col">Tgl & Jam Usulan</th>
                                <th scope="col">Topik</th>
                                <th scope="col">Status</th>
                                <th scope="col">Tgl Pengajuan</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $index => $request)
                                <tr>
                                    <th scope="row">{{ $requests->firstItem() + $index }}</th>
                                    <td>{{ $request->dosen->user->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($request->tanggal_usulan)->format('d M Y') }} <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($request->jam_usulan)->format('H:i') }}</small>
                                    </td>
                                    <td>{{ Str::limit($request->topik_bimbingan, 50) }}</td>
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
                                            <br><small class="text-info">Reschedule: {{ \Carbon\Carbon::parse($request->tanggal_dosen)->format('d M Y') }}, {{ \Carbon\Carbon::parse($request->jam_dosen)->format('H:i') }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $request->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <a href="{{ route('mahasiswa.request-bimbingan.show', $request->id) }}" class="btn btn-info btn-sm my-1" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($request->status_request == 'pending')
                                            <a href="{{ route('mahasiswa.request-bimbingan.edit', $request->id) }}" class="btn btn-warning btn-sm my-1" title="Edit Pengajuan">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('mahasiswa.request-bimbingan.destroy', $request->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan bimbingan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm my-1" title="Batalkan">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </form>
                                        @endif
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
                @if(Auth::user()->mahasiswa && Auth::user()->mahasiswa->dosen_pembimbing_id)
                    <div class="alert alert-info text-center">
                        Anda belum pernah mengajukan bimbingan.
                        <a href="{{ route('mahasiswa.request-bimbingan.create') }}" class="btn btn-link p-0 align-baseline">Ajukan sekarang?</a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
