<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// --- Controller Autentikasi Kustom ---
use App\Http\Controllers\Auth\CustomLoginController;

// --- Controller Aplikasi Lainnya ---
use App\Http\Controllers\DashboardController; // Controller utama pengarah dashboard

// Admin Controllers
// Tidak perlu 'use App\Http\Controllers\Admin\AdminDashboardController;' jika DashboardController utama yang menangani
use App\Http\Controllers\Admin\ProdiController;
use App\Http\Controllers\Admin\JenisDokumenController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\LogActivityController as AdminLogActivityController;
use App\Http\Controllers\Admin\DosenController as AdminDosenController;
use App\Http\Controllers\Admin\MahasiswaController as AdminMahasiswaController;

// Mahasiswa Controllers
// Tidak perlu 'use App\Http\Controllers\Mahasiswa\MahasiswaDashboardController;' jika DashboardController utama yang menangani
use App\Http\Controllers\Mahasiswa\RequestJudulController as MahasiswaRequestJudulController;
use App\Http\Controllers\Mahasiswa\RequestBimbinganController as MahasiswaRequestBimbinganController;
use App\Http\Controllers\Mahasiswa\DokumenController as MahasiswaDokumenController;
use App\Http\Controllers\Mahasiswa\HistoryBimbinganController as MahasiswaHistoryBimbinganController;

// Dosen Controllers
// Tidak perlu 'use App\Http\Controllers\Dosen\DosenDashboardController;' jika DashboardController utama yang menangani
use App\Http\Controllers\Dosen\RequestJudulController as DosenRequestJudulController;
use App\Http\Controllers\Dosen\RequestBimbinganController as DosenRequestBimbinganController;
use App\Http\Controllers\Dosen\HistoryBimbinganController as DosenHistoryBimbinganController;
use App\Http\Controllers\Dosen\ReviewDokumenController as DosenReviewDokumenController; // Controller untuk review dokumen

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Rute Autentikasi Kustom ---
Route::middleware('guest')->group(function () {
    Route::get('login', [CustomLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [CustomLoginController::class, 'login']);
});
Route::post('logout', [CustomLoginController::class, 'logout'])->middleware('auth')->name('logout');

// --- Halaman Awal ---
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// --- Rute yang Memerlukan Autentikasi ---
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Rute Spesifik Admin ---
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard admin ditangani oleh DashboardController utama atau Anda bisa definisikan AdminDashboardController di sini jika perlu.
        // Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('prodi', ProdiController::class);
        Route::resource('jenis-dokumen', JenisDokumenController::class)->except(['show']);
        Route::resource('users', AdminUserController::class); // CRUD User umum (admin, dosen, mhs jika dikelola di sini)
        Route::resource('dosen', AdminDosenController::class); // CRUD khusus Dosen (jika berbeda dari users)
        Route::resource('mahasiswa', AdminMahasiswaController::class); // CRUD khusus Mahasiswa (jika berbeda dari users)
        Route::resource('log-activities', AdminLogActivityController::class)->only(['index', 'show']);
    });

    // --- Rute Spesifik Dosen ---
    Route::middleware(['dosen'])->prefix('dosen')->name('dosen.')->group(function () {
        // Dashboard dosen ditangani oleh DashboardController utama atau Anda bisa definisikan DosenDashboardController di sini.
        // Route::get('/dashboard', [DosenDashboardController::class, 'index'])->name('dashboard');

        Route::resource('request-judul', DosenRequestJudulController::class)->only(['index', 'show', 'edit', 'update']);
        Route::resource('request-bimbingan', DosenRequestBimbinganController::class)->only(['index', 'show', 'edit', 'update']);
        Route::resource('history-bimbingan', DosenHistoryBimbinganController::class);
        Route::resource('history-bimbingan', DosenHistoryBimbinganController::class)->except(['create', 'store', 'destroy']);

        // Rute untuk Review Dokumen oleh Dosen
        Route::get('review-dokumen', [DosenReviewDokumenController::class, 'index'])
             ->name('review-dokumen.index');
        Route::get('review-dokumen/{dokumenProyekAkhir}/proses', [DosenReviewDokumenController::class, 'prosesReview'])
             ->name('review-dokumen.proses');
        Route::put('review-dokumen/{dokumenProyekAkhir}', [DosenReviewDokumenController::class, 'updateReview'])
             ->name('review-dokumen.update');
    });

    // --- Rute Spesifik Mahasiswa ---
    Route::middleware(['mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        // Dashboard mahasiswa ditangani oleh DashboardController utama atau Anda bisa definisikan MahasiswaDashboardController di sini.
        // Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');

        Route::resource('request-judul', MahasiswaRequestJudulController::class);
        Route::resource('request-bimbingan', MahasiswaRequestBimbinganController::class);
        Route::resource('dokumen', MahasiswaDokumenController::class); // Untuk mahasiswa submit dokumen
        Route::resource('history-bimbingan', MahasiswaHistoryBimbinganController::class)->only(['index', 'show']);
    });

});
