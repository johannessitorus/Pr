@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@section('content')
<div class="container py-4">
    <div class="row mb-3">
        <div class="col">
            <h2>Dashboard Mahasiswa</h2>
            <p class="lead">Selamat datang, {{ Auth::user()->name ?? 'Mahasiswa' }}!</p>
            @if(Auth::user()->mahasiswa) {{-- Asumsi relasi 'mahasiswa' dari model User --}}
                <p class="text-muted">NIM: {{ Auth::user()->mahasiswa->nim }} | Prodi: {{ Auth::user()->mahasiswa->prodi->nama_prodi ?? 'N/A' }}</p>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- Kolom Kiri: Kalender dan Progres Tugas Akhir --}}
        <div class="col-md-8 mb-4">
            {{-- KALENDER (Sama seperti dashboard dosen, jika diperlukan) --}}
            @if(isset($cal_currentMonthDateObject))
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('dashboard', $cal_previousMonthLinkParams) }}" class="btn btn-outline-primary btn-sm">< Prev</a>
                        <h5 class="mb-0">{{ $cal_monthName }} {{ $cal_year }}</h5>
                        <a href="{{ route('dashboard', $cal_nextMonthLinkParams) }}" class="btn btn-outline-primary btn-sm">Next ></a>
                    </div>
                </div>
                <div class="card-body p-2">
                    <table class="table table-bordered text-center calendar-table">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 14.28%;">Sen</th>
                                <th scope="col" style="width: 14.28%;">Sel</th>
                                <th scope="col" style="width: 14.28%;">Rab</th>
                                <th scope="col" style="width: 14.28%;">Kam</th>
                                <th scope="col" style="width: 14.28%;">Jum</th>
                                <th scope="col" style="width: 14.28%;">Sab</th>
                                <th scope="col" style="width: 14.28%;">Min</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            @php $dayCounter = 0; @endphp
                            @foreach ($cal_days as $day)
                                @if($loop->first && $day->dayOfWeekIso > 1)
                                    <td colspan="{{ $day->dayOfWeekIso - 1 }}" class="other-month"></td>
                                    @php $dayCounter += ($day->dayOfWeekIso - 1); @endphp
                                @endif

                                <td class="
                                    @if($day->month != $cal_currentMonthDateObject->month) other-month text-muted @endif
                                    @if($day->isSameDay($today)) bg-primary text-white today @endif
                                    {{-- @if(isset($jadwal_bimbingan[$day->toDateString()])) has-event @endif --}}
                                ">
                                    <div class="day-number">{{ $day->day }}</div>
                                    {{-- @if(isset($jadwal_bimbingan[$day->toDateString()]))
                                        @foreach($jadwal_bimbingan[$day->toDateString()] as $bimbingan)
                                            <div class="event-indicator bg-info text-white rounded px-1 small d-block mb-1" title="{{ $bimbingan['topik'] }}">
                                                Bimbingan
                                            </div>
                                        @endforeach
                                    @endif --}}
                                </td>

                                @php $dayCounter++; @endphp
                                @if($dayCounter % 7 == 0 && !$loop->last)
                                    </tr><tr>
                                @endif
                            @endforeach
                            @if($dayCounter % 7 != 0)
                                <td colspan="{{ 7 - ($dayCounter % 7) }}" class="other-month"></td>
                            @endif
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Status Proyek Akhir --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Status Proyek Akhir Anda</h5>
                </div>
                <div class="card-body">
                    @if(Auth::user()->mahasiswa && Auth::user()->mahasiswa->judul_proyek_akhir)
                        <h6 class="card-title">Judul: {{ Auth::user()->mahasiswa->judul_proyek_akhir }}</h6>
                        <p class="card-text">
                            Status Saat Ini:
                            <span class="badge
                                @switch(Auth::user()->mahasiswa->status_proyek_akhir)
                                    @case('belum_ada') bg-secondary @break
                                    @case('pengajuan_judul') bg-info @break
                                    @case('bimbingan') bg-primary @break
                                    @case('selesai') bg-success @break
                                    @case('revisi') bg-warning text-dark @break
                                    @default bg-light text-dark @break
                                @endswitch
                            ">
                                {{ ucwords(str_replace('_', ' ', Auth::user()->mahasiswa->status_proyek_akhir)) }}
                            </span>
                        </p>
                        <p>Dosen Pembimbing: {{ Auth::user()->mahasiswa->dosenPembimbing->user->name ?? 'Belum ditentukan' }}</p>
                        {{-- Tambahkan progress bar atau detail lain jika perlu --}}
                    @else
                        <p class="text-muted">Anda belum mengajukan judul proyek akhir atau status belum tersedia.</p>
                        <a href="{{ route('mahasiswa.request-judul.create') }}" class="btn btn-primary"><i class="fas fa-plus-circle me-1"></i> Ajukan Judul Sekarang</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Aksi Cepat dan Informasi --}}
        <div class="col-md-4">
            <div class="card mb-3 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Aksi Cepat</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('mahasiswa.request-judul.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-lightbulb me-2 text-warning"></i> Pengajuan Judul Saya
                    </a>
                    <a href="{{ route('mahasiswa.request-bimbingan.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-plus me-2 text-info"></i> Pengajuan Bimbingan Saya
                    </a>
                    <a href="{{ route('mahasiswa.dokumen.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-folder-open me-2 text-primary"></i> Dokumen Proyek Akhir Saya
                    </a>
                    <a href="{{ route('mahasiswa.history-bimbingan.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-history me-2 text-success"></i> Riwayat Bimbingan Saya
                    </a>
                    {{-- Tambahkan link lain yang relevan --}}
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Pengumuman (Contoh)</h5>
                </div>
                <div class="card-body">
                    @if(isset($announcements) && count($announcements) > 0)
                        @foreach($announcements as $announcement)
                            <div class="alert alert-info" role="alert">
                                <h6 class="alert-heading">{{ $announcement->title }}</h6>
                                <p>{{ Str::limit($announcement->content, 100) }}</p>
                                <hr>
                                <p class="mb-0"><small>Diposting: {{ $announcement->created_at->format('d M Y') }}</small></p>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">Tidak ada pengumuman terbaru.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- Jika ada CSS spesifik untuk kalender atau dashboard mahasiswa --}}
<style>
    .calendar-table td, .calendar-table th {
        height: 70px; /* Sesuaikan tinggi sel kalender mahasiswa jika berbeda */
        vertical-align: top;
        padding: 0.25rem;
    }
    .calendar-table .day-number {
        font-weight: bold;
        font-size: 0.9em;
        text-align: left;
        padding-left: 5px;
    }
    .calendar-table .other-month .day-number {
        color: #ccc;
    }
    .calendar-table .today {
        border: 2px solid #007bff;
        font-weight: bold;
    }
    .calendar-table .today .day-number {
        color: white;
    }
    .event-indicator {
        font-size: 0.7em;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endpush
