@extends('layouts.app') {{-- Sesuaikan dengan nama layout utama mahasiswa Anda --}}

@section('title', 'History Bimbingan Saya')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar History Bimbingan</h6>
        </div>
        <div class="card-body">
            {{-- Notifikasi Sukses/Error --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Filter Status --}}
            <form method="GET" action="{{ route('mahasiswa.history-bimbingan.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label for="status_filter" class="form-label">Filter berdasarkan Status:</label>
                        <select name="status_filter" id="status_filter" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            @if(isset($statusesForFilter))
                                @foreach($statusesForFilter as $status)
                                    <option value="{{ $status }}" {{ request('status_filter') == $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </form>

            @if($histories->isEmpty())
                <div class="alert alert-info">
                    Belum ada history bimbingan yang tercatat.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Pertemuan Ke</th>
                                <th>Tanggal Bimbingan</th>
                                <th>Topik</th>
                                <th>Dosen</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($histories as $index => $history)
                            <tr>
                                <td>{{ $histories->firstItem() + $index }}</td>
                                <td>{{ $history->pertemuan_ke }}</td>
                                <td>{{ $history->tanggal_bimbingan->format('d M Y, H:i') }} WIB</td>
                                <td>{{ Str::limit($history->topik, 50) }}</td>
                                <td>{{ $history->dosen && $history->dosen->user ? $history->dosen->user->name : 'N/A' }}</td>
                                <td>
                                    <span class="badge
                                        @if($history->status_kehadiran == 'hadir' || $history->status_kehadiran == 'selesai') bg-success
                                        @elseif(in_array($history->status_kehadiran, ['tidak_hadir_mahasiswa', 'tidak_hadir_dosen', 'dibatalkan_mahasiswa', 'dibatalkan_dosen'])) bg-danger
                                        @elseif($history->status_kehadiran == 'terjadwal' || $history->status_kehadiran == 'dijadwalkan_ulang') bg-info text-dark
                                        @else bg-secondary @endif">
                                        {{ ucfirst(str_replace('_', ' ', $history->status_kehadiran)) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('mahasiswa.history-bimbingan.show', $history->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    {{-- Tambahkan tombol lain jika ada aksi, misal edit/batal --}}
                                    {{-- @if(in_array($history->status_kehadiran, ['terjadwal']))
                                        <a href="{{ route('mahasiswa.history-bimbingan.edit', $history->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Batalkan
                                        </a>
                                    @endif --}}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $histories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Tambahkan script JS jika diperlukan, misal untuk DataTables atau confirm dialog --}}
@endpush
