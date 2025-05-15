@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="mb-0">Dashboard Admin</h1>
            <p class="text-muted">Selamat datang, {{ Auth::user()->name ?? 'Administrator' }}!</p>
        </div>
    </div>

    {{-- Baris untuk Statistik Ringkas --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title">Total Pengguna</h5>
                            <p class="card-text fs-3 fw-bold">{{ $totalUsers ?? 'N/A' }}</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.users.index') }}" class="card-footer text-white clearfix small z-1">
                    <span class="float-start">Lihat Detail</span>
                    <span class="float-end"><i class="fas fa-arrow-circle-right"></i></span>
                </a>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                     <div class="row">
                        <div class="col">
                            <h5 class="card-title">Total Prodi</h5>
                            <p class="card-text fs-3 fw-bold">{{ $totalProdi ?? 'N/A' }}</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.prodi.index') }}" class="card-footer text-white clearfix small z-1">
                    <span class="float-start">Lihat Detail</span>
                    <span class="float-end"><i class="fas fa-arrow-circle-right"></i></span>
                </a>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-dark">Total Dosen</h5>
                            <p class="card-text fs-3 fw-bold text-dark">{{ $totalDosen ?? 'N/A' }}</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-3x opacity-50 text-dark"></i>
                        </div>
                    </div>
                </div>
                 <a href="{{ route('admin.users.index', ['role' => 'dosen']) }}" class="card-footer text-dark clearfix small z-1">
                    <span class="float-start">Lihat Detail</span>
                    <span class="float-end"><i class="fas fa-arrow-circle-right"></i></span>
                </a>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title">Total Mahasiswa</h5>
                            <p class="card-text fs-3 fw-bold">{{ $totalMahasiswa ?? 'N/A' }}</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.mahasiswa.index', ['role' => 'mahasiswa']) }}" class="card-footer text-white clearfix small z-1">
                    <span class="float-start">Lihat Detail</span>
                    <span class="float-end"><i class="fas fa-arrow-circle-right"></i></span>
                </a>
            </div>
        </div>
    </div>

    {{-- Baris untuk Aksi Cepat dan Informasi Lain --}}
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Manajemen Sistem</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.prodi.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-graduation-cap me-2 text-primary"></i> Kelola Program Studi
                    </a>
                    <a href="{{ route('admin.jenis-dokumen.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-alt me-2 text-success"></i> Kelola Jenis Dokumen
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-users-cog me-2 text-info"></i> Kelola Pengguna (Mahasiswa, Dosen, Admin)
                    </a>
                    <a href="{{ route('admin.log-activities.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-clipboard-list me-2 text-warning"></i> Lihat Log Aktivitas Sistem
                    </a>
                    {{-- Tambahkan link manajemen lainnya --}}
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Aktivitas Terbaru (Contoh)</h5>
                </div>
                <div class="card-body">
                    @if(isset($recentActivities) && count($recentActivities) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($recentActivities as $activity)
                                <li class="list-group-item">
                                    <small class="text-muted float-end">{{ $activity->created_at->diffForHumans() }}</small>
                                    <strong>{{ $activity->user->name ?? 'Sistem' }}</strong>: {{ $activity->description }}
                                    @if($activity->properties)
                                        <br><small class="text-muted">{{ json_encode($activity->properties) }}</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                         <a href="{{ route('admin.log-activities.index') }}" class="btn btn-outline-primary btn-sm mt-3">Lihat Semua Log</a>
                    @else
                        <p class="text-muted">Belum ada aktivitas terbaru.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-footer {
        text-decoration: none;
    }
    .card-footer:hover {
        background-color: rgba(0,0,0,0.1);
    }
    .opacity-50 {
        opacity: 0.5;
    }
</style>
@endpush
