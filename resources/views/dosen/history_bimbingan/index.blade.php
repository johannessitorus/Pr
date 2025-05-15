@extends('layouts.app') {{-- Sesuaikan dengan nama layout utama dosen Anda --}}

@section('title', 'Management History Bimbingan')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar History Bimbingan Mahasiswa</h6>
        </div>
        <div class="card-body">
            @include('partials.alerts') {{-- Jika Anda punya file alert terpusat --}}

            {{-- Filter --}}
            <form method="GET" action="{{ route('dosen.history-bimbingan.index') }}" class="mb-4">
                <div class="row g-3 align-items-end">
                    {{-- Jika Anda ingin filter per mahasiswa, uncomment dan implementasikan logic di controller --}}
                    {{-- <div class="col-md-4">
                        <label for="mahasiswa_filter" class="form-label">Filter Mahasiswa:</label>
                        <select name="mahasiswa_filter" id="mahasiswa_filter" class="form-select">
                            <option value="">-- Semua Mahasiswa Bimbingan --</option>
                            @if(isset($mahasiswasForFilter))
                                @foreach($mahasiswasForFilter as $mhs)
                                    <option value="{{ $mhs->id }}" {{ request('mahasiswa_filter') == $mhs->id ? 'selected' : '' }}>
                                        {{ $mhs->user->name }} ({{ $mhs->nim }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div> --}}
                    <div class="col-md-4">
                        <label for="status_filter" class="form-label">Filter Status Kehadiran:</label>
                        <select name="status_filter" id="status_filter" class="form-select">
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
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                     <div class="col-md-2">
                        <a href="{{ route('dosen.history-bimbingan.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>

            @if($histories->isEmpty())
                <div class="alert alert-info">
                    Belum ada history bimbingan yang tercatat atau sesuai dengan filter Anda.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dataTableHistory" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Mahasiswa</th>
                                <th>Tgl. Bimbingan</th>
                                <th>Topik Awal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($histories as $index => $history)
                            <tr>
                                <td>{{ $histories->firstItem() + $index }}</td>
                                <td>
                                    @if($history->mahasiswa && $history->mahasiswa->user)
                                        {{ $history->mahasiswa->user->name }}
                                        <br><small>NIM: {{ $history->mahasiswa->nim ?? '-' }}</small>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $history->tanggal_bimbingan->format('d M Y, H:i') }}</td>
                                <td>
                                    {{ Str::limit($history->requestBimbingan->topik_bimbingan ?? $history->topik, 40) }}
                                </td>
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
                                    <a href="{{ route('dosen.history-bimbingan.show', $history->id) }}" class="btn btn-sm btn-info mb-1" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    {{-- Tombol Edit hanya muncul jika status masih bisa diubah atau dosen perlu menambah catatan --}}
                                    @if(in_array($history->status_kehadiran, ['terjadwal', 'hadir']))
                                    <a href="{{ route('dosen.history-bimbingan.edit', $history->id) }}" class="btn btn-sm btn-warning mb-1" title="Proses/Update Status">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @elseif($history->status_kehadiran == 'selesai' && empty($history->catatan_dosen))
                                     <a href="{{ route('dosen.history-bimbingan.edit', $history->id) }}" class="btn btn-sm btn-success mb-1" title="Tambah Catatan">
                                        <i class="fas fa-comment-dots"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $histories->appends(request()->query())->links() }} {{-- Penting: appends() agar filter tetap saat paginasi --}}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- <script>
    // Jika menggunakan DataTables
    // $(document).ready(function() {
    //     $('#dataTableHistory').DataTable();
    // });
</script> --}}
@endpush
