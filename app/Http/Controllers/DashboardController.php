<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

// Model yang mungkin diperlukan
use App\Models\User;
use App\Models\Prodi;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\DokumenProyekAkhir; // Untuk data dosen
use Spatie\Activitylog\Models\Activity; // Jika menggunakan Spatie Activity Log

class DashboardController extends Controller
{
    /**
     * Menghasilkan data untuk kalender.
     */
    private function getCalendarData(Request $request): array
    {
        $requestedMonth = (int) $request->input('cal_month', Carbon::now()->month);
        $requestedYear = (int) $request->input('cal_year', Carbon::now()->year);

        $currentMonthDate = Carbon::createFromDate($requestedYear, $requestedMonth, 1)->startOfDay();
        $previousMonthDate = $currentMonthDate->copy()->subMonthNoOverflow();
        $nextMonthDate = $currentMonthDate->copy()->addMonthNoOverflow();
        $startDate = $currentMonthDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endDate = $currentMonthDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $days = CarbonPeriod::create($startDate, $endDate);

        return [
            'cal_currentMonthDateObject' => $currentMonthDate,
            'cal_monthName' => $currentMonthDate->translatedFormat('F'),
            'cal_year' => $currentMonthDate->year,
            'cal_days' => $days,
            'cal_previousMonthLinkParams' => ['cal_month' => $previousMonthDate->month, 'cal_year' => $previousMonthDate->year],
            'cal_nextMonthLinkParams' => ['cal_month' => $nextMonthDate->month, 'cal_year' => $nextMonthDate->year],
            'today' => Carbon::today(),
        ];
    }

    /**
     * Mengambil data statistik untuk dashboard admin.
     */
    private function getAdminDashboardData(): array
    {
        $data = [];
        $data['totalUsers'] = User::count();
        $data['totalProdi'] = Prodi::count();
        $data['totalDosen'] = User::where('role', 'dosen')->count(); // Asumsi dosen adalah User dengan role 'dosen'
        $data['totalMahasiswa'] = User::where('role', 'mahasiswa')->count(); // Asumsi mahasiswa adalah User dengan role 'mahasiswa'

        // Aktivitas Terbaru (Uncomment dan pastikan package Spatie Activity Log terinstal dan dikonfigurasi)
        //$data['recentActivities'] = [];
        //if (class_exists(Activity::class)) {
            //$data['recentActivities'] = Activity::with('causer') // Eager load causer
                                          //->latest()
                                          //->take(5) // Ambil 5 aktivitas terbaru
                                          //->get();
        //}
        return $data;
    }

    /**
     * Mengambil data spesifik untuk dashboard dosen.
     */
    private function getDosenDashboardData(User $user): array
    {
        $data = [];
        $dosen = $user->dosen; // Asumsi relasi 'dosen' ada di model User (User->dosen())

        if ($dosen) {
            // Ambil ID mahasiswa yang menjadi bimbingan dosen ini
            $mahasiswaBimbinganIds = Mahasiswa::where('dosen_pembimbing_id', $dosen->id)->pluck('id');

            if ($mahasiswaBimbinganIds->isNotEmpty()) {
                $data['dokumenPendingReview'] = DokumenProyekAkhir::whereIn('mahasiswa_id', $mahasiswaBimbinganIds)
                    ->whereIn('status_review', ['pending', 'revision_needed']) // Ambil pending dan perlu revisi
                    ->with(['mahasiswa.user', 'jenisDokumen'])
                    ->latest('updated_at')
                    ->take(5)
                    ->get();
            } else {
                $data['dokumenPendingReview'] = collect(); // Collection kosong jika tidak ada mhs bimbingan
            }

            $data['mahasiswa_bimbingan'] = Mahasiswa::where('dosen_pembimbing_id', $dosen->id)
                                                ->whereIn('status_proyek_akhir', ['bimbingan', 'pengajuan_judul', 'revisi']) // Sesuaikan status aktif
                                                ->with('user') // Eager load user dari mahasiswa
                                                ->orderBy('created_at', 'desc') // Atau urutan lain
                                                ->get();
        } else {
            $data['dokumenPendingReview'] = collect();
            $data['mahasiswa_bimbingan'] = collect();
        }
        return $data;
    }

    /**
     * Mengambil data spesifik untuk dashboard mahasiswa.
     */
    private function getMahasiswaDashboardData(User $user): array
    {
        $data = [];
        $mahasiswa = $user->mahasiswa; // Asumsi relasi 'mahasiswa' ada di model User (User->mahasiswa())

        if ($mahasiswa) {
            $data['status_proyek_akhir'] = $mahasiswa->status_proyek_akhir ?? 'N/A';
            // Anda bisa menambahkan data lain yang relevan untuk mahasiswa di sini
            // Misalnya, pengajuan judul terakhir, bimbingan terjadwal, dll.
            // $data['request_judul_terakhir'] = $mahasiswa->requestJuduls()->latest()->first();
        } else {
            $data['status_proyek_akhir'] = 'N/A';
        }
        return $data;
    }


    public function index(Request $request)
    {
        $user = Auth::user();
        $viewData = []; // Inisialisasi viewData

        if (!$user) {
            return redirect()->route('login')->withErrors('Sesi tidak valid. Silakan login kembali.');
        }

        // Ambil data kalender jika bukan admin
        if ($user->role !== 'admin') {
            $viewData = array_merge($viewData, $this->getCalendarData($request));
        }

        // Proses berdasarkan role
        if ($user->role === 'admin') {
            $adminData = $this->getAdminDashboardData();
            $viewData = array_merge($viewData, $adminData);
            return view('dashboards.admin', $viewData); // Pastikan path view benar: admin/dashboard.blade.php

        } elseif ($user->role === 'dosen') {
            $dosenData = $this->getDosenDashboardData($user);
            $viewData = array_merge($viewData, $dosenData);
            return view('dashboards.dosen', $viewData); // Pastikan path view benar: dosen/dashboard.blade.php

        } elseif ($user->role === 'mahasiswa') {
            $mahasiswaData = $this->getMahasiswaDashboardData($user);
            $viewData = array_merge($viewData, $mahasiswaData);
            return view('dashboards.mahasiswa', $viewData); // Pastikan path view benar: mahasiswa/dashboard.blade.php
        }

        // Jika role tidak dikenali, logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->withErrors('Peran pengguna tidak valid atau tidak dikenali.');
    }
}
