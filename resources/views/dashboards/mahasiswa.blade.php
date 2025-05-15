@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}"> {{-- CSS utama Anda --}}
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css' rel='stylesheet' />
@endpush


@section('content')
<div class="dashboard-container">

    <div class="main-content">

        {{-- Baris 1: Welcome Card & Status Proyek Akhir --}}
        <div class="row mb-2">
            {{-- Kolom Kiri: Welcome Card --}}
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="card welcome-card shadow-sm h-100">
                    <div class="card-body d-flex">
                    <img src="{{ asset('foto/biodata.PNG') }}" class="baground-light me-3" style="width: 150px; height: auto;">                        <div>
                            <h2><b>Selamat Datang, SIPA Vokasi <br>IT DEL,{{ Auth::user()->name ?? 'Pengguna' }}!</b></h2>
                            <p class="text-muted">Data Anda telah terverifikasi:</p>
                            <p>Nama :{{ Auth::user()->name ?? 'Pengguna' }}
                            <br>NIM :{{ Auth::user()->mahasiswa->nim }}
                            <br>Prodi: {{ Auth::user()->mahasiswa->prodi->nama_prodi ?? 'N/A' }}</p>
                            <p class="text-muted">Manfaatkan SIPA untuk perjalanan akademik yang lebih terorganisir.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Status Proyek Akhir --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
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
                        @else
                            <p class="text-muted">Anda belum mengajukan judul proyek akhir atau status belum tersedia.</p>
                            <a href="{{ route('mahasiswa.request-judul.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus-circle me-1"></i> Ajukan Judul Sekarang</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Baris 2: Aksi Cepat (Kiri) & Kalender/Pengumuman (Kanan) --}}
        <div class="row">
            {{-- Kolom Kiri (lebih besar): Aksi Cepat --}}
            <div class="col-md-8">
                {{-- Baris Aksi Cepat 1 --}}
                <div class="row mb-4">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <a href="{{ route('mahasiswa.request-judul.index') }}" class="text-decoration-none text-dark">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas fa-lightbulb fa-2x text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4>Pengajuan Judul</h4>
                                       <p>Mahasiswa mengusulkan judul proyek akhir</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('mahasiswa.request-bimbingan.index') }}" class="text-decoration-none text-dark">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas fa-calendar-plus fa-2x text-info"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4>Pengajuan Bimbingan</h4>
                                        <p>Ajukan sesi bimbingan dengan jadwal dan topic yang dibahas</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Baris Aksi Cepat 2 --}}
                <div class="row mb-4"> {{-- Tambah mb-4 jika ada konten lain di bawah col-md-8 --}}
                    <div class="col-md-6 mb-4 mb-md-0">
                        <a href="{{ route('mahasiswa.dokumen.index') }}" class="text-decoration-none text-dark">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas fa-folder-open fa-2x text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4>Dokumen Proyek Akhir</h4>
                                        <p>Membantu mendokumentasikan setiap proses pengembangan Proyek Akhir</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('mahasiswa.history-bimbingan.index') }}" class="text-decoration-none text-dark">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas fa-history fa-2x text-success"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                    <h4>Riwayat Bimbingan</h4>
                                     <p>Membantu mencatat setiap proses bimbingan Proyek Akhir sebelumnya secara lengkap</p>                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                {{-- Jika ada elemen lain di kolom kiri ini, bisa ditambahkan di sini --}}
            </div>

            {{-- Kolom Kanan (lebih kecil): Kalender & Pengumuman --}}
            <div class="col-md-4">
                {{-- KALENDER --}}
                @if(isset($cal_currentMonthDateObject))
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('dashboard', $cal_previousMonthLinkParams) }}" class="btn btn-outline-primary btn-sm py-0 px-1">< Prev</a>
                            <h6 class="mb-0 small-calendar-header">{{ $cal_monthName }} {{ $cal_year }}</h6>
                            <a href="{{ route('dashboard', $cal_nextMonthLinkParams) }}" class="btn btn-outline-primary btn-sm py-0 px-1">Next ></a>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-bordered text-center calendar-table small-calendar">
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
                                    ">
                                        <div class="day-number">{{ $day->day }}</div>
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

                {{-- Pengumuman --}}
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Pengumuman</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($announcements) && count($announcements) > 0)
                            @foreach($announcements as $index => $announcement)
                                <div class="alert alert-info mb-2 p-2" role="alert">
                                    <h6 class="alert-heading small">{{ $announcement->title }}</h6>
                                    <p class="mb-1 x-small">{{ Str::limit($announcement->content, 70) }}</p>
                                    <p class="mb-0 xx-small"><small>Diposting: {{ $announcement->created_at->format('d M Y') }}</small></p>
                                </div>
                                @if($index >= 1) @break @endif {{-- Batasi jumlah pengumuman jika perlu --}}
                            @endforeach
                        @else
                            <p class="text-muted">Tidak ada pengumuman terbaru.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
        /* Penting: Pastikan nilai padding-top ini sesuai dengan tinggi navbar Anda */
        body {
            padding-top: 65px; /* CONTOH: GANTI DENGAN TINGGI NAVBAR AKTUAL */
        }

        .dashboard-container .main-content {
            /* padding: 1rem; */
        }

        .welcome-card .avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        .welcome-card .welcome-text h5,
        .welcome-card .welcome-text h6 {
            margin-bottom: 0.25rem;
        }
        .welcome-card .card-body {
            align-items: center;
        }

        /* Styles for a SMALLER calendar in the sidebar */
        .small-calendar th, .small-calendar td {
            height: auto;
            padding: 2px !important;
            font-size: 0.75rem;
            vertical-align: top;
        }
        .small-calendar .day-number {
            font-weight: bold;
            font-size: 0.8em;
            text-align: center;
            padding: 2px 0;
            display: block;
            line-height: 1.2;
        }
        .small-calendar .other-month .day-number {
            color: #ccc !important;
        }
        .small-calendar .today {
            background-color: #007bff !important;
            color: white !important;
            border-radius: 50%;
            font-weight: bold;
        }
        .small-calendar .today .day-number {
            color: white !important;
        }

        .small-calendar-header {
            font-size: 0.9rem;
            font-weight: 500;
        }
        .small-calendar .btn-sm {
            padding: 0.1rem 0.3rem;
            font-size: 0.75rem;
        }

        .x-small {
            font-size: 0.75rem;
        }
        .xx-small {
            font-size: 0.65rem;
        }

        .card-sidebar-kalender {
            min-height: 350px;
        }
        .card-sidebar-status {
            min-height: 200px;
        }
        .card-action-item .card-body {
             display: flex;
             align-items: center;
        }
        .card-action-item .flex-shrink-0 {
            margin-right: 1rem;
        }
    </style>
    @endpush
