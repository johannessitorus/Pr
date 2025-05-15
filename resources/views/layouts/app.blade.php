<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Sistem Informasi')</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    {{-- Font Awesome CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Custom App CSS (Jika ada) --}}
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}

    @stack('styles') {{-- Untuk CSS spesifik halaman --}}

    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: 'Nunito', sans-serif;
        }
        .main-content {
            flex: 1;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 1rem 0;
            text-align: center;
            font-size: 0.9em;
            color: #6c757d;
        }
        /* Style untuk sidebar jika Anda menggunakannya nanti */
        .sidebar {
            min-height: 100vh; /* Full height */
            position: fixed;
            top: 0;
            left: 0;
            width: 250px; /* Atur lebar sidebar */
            z-index: 100; /* Stay on top */
            padding-top: 56px; /* Space for navbar */
            background-color: #343a40; /* Warna sidebar */
            color: #adb5bd;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 1.1em;
            color: #adb5bd;
            display: block;
        }
        .sidebar a:hover, .sidebar a.active {
            color: #fff;
            background-color: #495057;
        }
        .content-wrapper {
             /* margin-left: 250px; Sesuaikan jika menggunakan sidebar fix */
            padding-top: 56px; /* Space untuk navbar atas */
        }
        .navbar-brand-custom {
            font-weight: bold;
            color: #fff !important; /* Pastikan warna teks brand kontras dengan background navbar */
        }
    </style>

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm fixed-top">
            <div class="container">
                <a class="navbar-brand navbar-brand-custom" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            {{-- Navigasi berdasarkan role --}}
                            @if(Auth::user()->role === 'admin')
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.prodi*') || request()->routeIs('admin.jenis-dokumen*') || request()->routeIs('admin.users*') ? 'active' : '' }}" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Manajemen Admin
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.prodi*') ? 'active' : '' }}" href="{{ route('admin.prodi.index') }}">Prodi</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.jenis-dokumen*') ? 'active' : '' }}" href="{{ route('admin.jenis-dokumen.index') }}">Jenis Dokumen</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Pengguna</a></li>
                                        <li><a class="dropdown-item {{ request()->routeIs('admin.log-activities*') ? 'active' : '' }}" href="{{ route('admin.log-activities.index') }}">Log Aktivitas</a></li>
                                    </ul>
                                </li>
                            @elseif(Auth::user()->role === 'dosen')
                                {{-- Tambahkan navigasi spesifik dosen di navbar jika perlu --}}
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('dosen.request-judul*') ? 'active' : '' }}" href="{{ route('dosen.request-judul.index') }}">Request Judul</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('dosen.request-bimbingan*') ? 'active' : '' }}" href="{{ route('dosen.request-bimbingan.index') }}">Request Bimbingan</a>
                                </li>
                            @elseif(Auth::user()->role === 'mahasiswa')
                                {{-- Tambahkan navigasi spesifik mahasiswa di navbar jika perlu --}}
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('mahasiswa.request-judul*') ? 'active' : '' }}" href="{{ route('mahasiswa.request-judul.index') }}">Request Judul</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('mahasiswa.dokumen*') ? 'active' : '' }}" href="{{ route('mahasiswa.dokumen.index') }}">Dokumen Saya</a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            {{-- @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif --}}
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-user-cog me-2"></i> Profil Saya
                                    </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        {{-- Jika Anda ingin menggunakan sidebar tetap --}}
        {{-- @auth
            <nav class="sidebar d-none d-md-block">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        @if(Auth::user()->role === 'admin')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.prodi*') ? 'active' : '' }}" href="{{ route('admin.prodi.index') }}">
                                    <i class="fas fa-graduation-cap me-2"></i> Prodi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.jenis-dokumen*') ? 'active' : '' }}" href="{{ route('admin.jenis-dokumen.index') }}">
                                    <i class="fas fa-file-alt me-2"></i> Jenis Dokumen
                                </a>
                            </li>
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                    <i class="fas fa-users me-2"></i> Pengguna
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.log-activities*') ? 'active' : '' }}" href="{{ route('admin.log-activities.index') }}">
                                    <i class="fas fa-clipboard-list me-2"></i> Log Aktivitas
                                </a>
                            </li>
                        @elseif(Auth::user()->role === 'dosen')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dosen.request-judul*') ? 'active' : '' }}" href="{{ route('dosen.request-judul.index') }}">
                                    <i class="fas fa-file-signature me-2"></i> Request Judul
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dosen.request-bimbingan*') ? 'active' : '' }}" href="{{ route('dosen.request-bimbingan.index') }}">
                                    <i class="fas fa-comments me-2"></i> Request Bimbingan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dosen.history-bimbingan*') ? 'active' : '' }}" href="{{ route('dosen.history-bimbingan.index') }}">
                                    <i class="fas fa-history me-2"></i> Riwayat Bimbingan
                                </a>
                            </li>
                        @elseif(Auth::user()->role === 'mahasiswa')
                             <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.request-judul*') ? 'active' : '' }}" href="{{ route('mahasiswa.request-judul.index') }}">
                                    <i class="fas fa-lightbulb me-2"></i> Ajukan Judul
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.request-bimbingan*') ? 'active' : '' }}" href="{{ route('mahasiswa.request-bimbingan.index') }}">
                                    <i class="fas fa-calendar-check me-2"></i> Ajukan Bimbingan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.dokumen*') ? 'active' : '' }}" href="{{ route('mahasiswa.dokumen.index') }}">
                                    <i class="fas fa-folder-open me-2"></i> Dokumen Proyek
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.history-bimbingan*') ? 'active' : '' }}" href="{{ route('mahasiswa.history-bimbingan.index') }}">
                                    <i class="fas fa-stream me-2"></i> Riwayat Bimbingan
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </nav>
        @endauth --}}

        <main class="py-4 main-content content-wrapper"> {{-- Tambahkan class content-wrapper jika menggunakan sidebar fixed --}}
            @yield('content')
        </main>

        <footer class="footer mt-auto">
            <div class="container">
                <span>© {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</span>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    {{-- Bootstrap 5 JS Bundle (Popper.js disertakan) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    {{-- Custom App JS (Jika ada) --}}
    {{-- <script src="{{ asset('js/app.js') }}" defer></script> --}}

    @stack('scripts') {{-- Untuk JS spesifik halaman --}}
</body>
</html>
