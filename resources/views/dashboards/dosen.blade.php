@extends('layouts.app') {{-- Atau layout spesifik dosen --}}

@section('title', 'Dashboard Dosen')

@section('content')
<div class="container py-4">
    <div class="row mb-3">
        <div class="col">
            <h2>Dashboard Dosen</h2>
            <p class="lead">Selamat datang kembali, {{ Auth::user()->name ?? 'Dosen' }}!</p>
        </div>
    </div>

    <div class="row">
        {{-- Kolom Kiri: Kalender (Tetap Sama seperti sebelumnya) --}}
        <div class="col-md-8 mb-4">
            {{-- ... Kode Kalender Anda ... --}}
            {{-- Pastikan kode kalender Anda sudah benar dan variabelnya tersedia --}}
             <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('dashboard', $cal_previousMonthLinkParams ?? []) }}" class="btn btn-outline-primary btn-sm">< Prev</a>
                        <h5 class="mb-0">{{ $cal_monthName ?? 'Bulan' }} {{ $cal_year ?? 'Tahun' }}</h5>
                        <a href="{{ route('dashboard', $cal_nextMonthLinkParams ?? []) }}" class="btn btn-outline-primary btn-sm">Next ></a>
                    </div>
                </div>
                <div class="card-body p-2">
                    @if(isset($cal_days) && isset($cal_currentMonthDateObject) && isset($today))
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
                                    @if(isset($events[$day->toDateString()])) has-event @endif
                                ">
                                    <div class="day-number">{{ $day->day }}</div>
                                    @if(isset($events[$day->toDateString()]))
                                        @foreach($events[$day->toDateString()] as $event)
                                            <div class="event-indicator bg-success text-white rounded px-1 small d-block mb-1" title="{{ $event['title'] }}">
                                                {{ Str::limit($event['title'], 8) }}
                                            </div>
                                        @endforeach
                                    @endif
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
                    @else
                    <p class="text-center text-muted">Data kalender tidak tersedia.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Aksi atau Informasi Cepat --}}
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    Aksi Cepat
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('dosen.request-judul.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-alt me-2"></i>Request Judul Masuk
                    </a>
                    <a href="{{ route('dosen.request-bimbingan.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-comments me-2"></i>Request Bimbingan Masuk
                    </a>
                    <a href="{{ route('dosen.history-bimbingan.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-history me-2"></i>Manajemen Riwayat Bimbingan
                    </a>
                    {{-- Link baru untuk melihat semua dokumen yang perlu direview --}}
                    <a href="{{ route('dosen.review-dokumen.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-folder-open me-2"></i>Review Dokumen Mahasiswa
                    </a>
                </div>
            </div>

            {{-- BAGIAN DOKUMEN MENUNGGU REVIEW (Dengan Link yang Diperbarui) --}}
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-hourglass-half me-2"></i>Dokumen Menunggu Review Anda
                </div>
                <div class="card-body p-0">
                    @if(isset($dokumenPendingReview) && $dokumenPendingReview->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($dokumenPendingReview as $dokumen)
                                <li class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            {{-- Link ke halaman proses review spesifik untuk dokumen ini --}}
                                            <a href="{{ route('dosen.review-dokumen.proses', $dokumen->id) }}" title="Review {{ $dokumen->jenisDokumen->nama_jenis ?? '' }} dari {{ $dokumen->mahasiswa->user->name ?? '' }}">
                                                {{ $dokumen->jenisDokumen->nama_jenis ?? 'Jenis Tidak Diketahui' }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $dokumen->updated_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1 small">
                                        Oleh: {{ $dokumen->mahasiswa->user->name ?? 'N/A' }} ({{ $dokumen->mahasiswa->nim ?? 'N/A' }})
                                        <br>
                                        File: <a href="{{ Storage::url($dokumen->file_path) }}" target="_blank">{{ Str::limit($dokumen->nama_file_asli, 25) }}</a>
                                        (v{{ $dokumen->versi }})
                                    </p>
                                    {{-- Tombol untuk langsung review --}}
                                    <a href="{{ route('dosen.review-dokumen.proses', $dokumen->id) }}" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-search-plus me-1"></i> Proses Review
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-3">
                            <p class="text-muted mb-0">Tidak ada dokumen yang menunggu review dari mahasiswa bimbingan Anda.</p>
                        </div>
                    @endif
                </div>
                 @if(isset($dokumenPendingReview) && $dokumenPendingReview->count() > 0)
                <div class="card-footer text-center">
                    {{-- Link ke halaman daftar semua dokumen pending untuk direview --}}
                    <a href="{{ route('dosen.review-dokumen.index') }}" class="small">Lihat Semua Dokumen Pending</a>
                </div>
                @endif
            </div>
            {{-- AKHIR BAGIAN DOKUMEN MENUNGGU REVIEW --}}


            <div class="card">
                <div class="card-header">
                    Mahasiswa Bimbingan Aktif
                </div>
                <div class="card-body">
                    @if(isset($mahasiswa_bimbingan) && $mahasiswa_bimbingan->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($mahasiswa_bimbingan as $mhs)
                                <li class="list-group-item px-0 py-2">
                                    {{-- Anda bisa membuat link ini ke halaman detail mahasiswa bimbingan jika ada --}}
                                    <a href="#">{{ $mhs->user->name ?? 'Nama Tidak Ada' }} ({{ $mhs->nim ?? 'NIM Tidak Ada' }})</a>
                                    <br><small class="text-muted">Status: {{ Str::title(str_replace('_', ' ', $mhs->status_proyek_akhir ?? 'N/A')) }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">Belum ada mahasiswa bimbingan aktif.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
{{-- ... CSS Anda ... --}}
<style>
    .calendar-table td, .calendar-table th {
        height: 80px; /* Atur tinggi sel kalender */
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
        border: 2px solid #007bff; /* Highlight hari ini */
        font-weight: bold;
    }
    .calendar-table .today .day-number {
        color: white;
    }
    .has-event {
        /* background-color: #e9f5ff; */ /* Warna latar untuk hari dengan event */
    }
    .event-indicator {
        font-size: 0.7em;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endpush

@endsection
