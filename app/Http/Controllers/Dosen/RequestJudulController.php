<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\RequestJudul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RequestJudulController extends Controller
{
    /**
     * Display a listing of the resource.
     * Menampilkan request judul yang ditujukan kepada dosen yang sedang login.
     */
    public function index(Request $request)
    {
        $userDosen = Auth::user(); // Dapatkan user dosen yang login

        if (!$userDosen || !$userDosen->dosen) {
            Log::warning('Data dosen tidak ditemukan untuk user ID: ' . ($userDosen->id ?? 'null') . ' saat mengakses index request judul dosen.');
            return redirect()->route('dashboard') // Atau ke dashboard dosen jika ada
                             ->with('error', 'Data profil dosen Anda tidak ditemukan atau tidak lengkap.');
        }
        $dosenId = $userDosen->dosen->id; // Dapatkan ID dari tabel 'dosen'

        try {
            // Ambil request judul yang 'dosen_tujuan_id' nya adalah ID dosen yang login
            // dan mungkin filter berdasarkan status tertentu (misalnya, hanya yang 'pending')
            $query = RequestJudul::where('dosen_tujuan_id', $dosenId);

            // Opsional: Filter berdasarkan status dari query string
            if ($request->has('status') && in_array($request->input('status'), ['pending', 'approved', 'rejected'])) {
                $query->where('status', $request->input('status'));
            } else {
                // Default, tampilkan yang pending, atau semua jika tidak ada filter status
                $query->where('status', 'pending'); // Contoh: default hanya tampilkan yang pending
            }

            $requests = $query->with(['mahasiswa.user:id,name', 'mahasiswa.prodi:id,nama_prodi']) // Eager load data mahasiswa & user mahasiswa & prodi mahasiswa
                                ->orderBy('created_at', 'desc') // Urutkan, misal yang terbaru dulu
                                ->paginate(10);

            $filterStatus = $request->input('status', 'pending'); // Untuk dikirim ke view agar tahu filter aktif

        } catch (\Throwable $e) {
            Log::error("Error saat mengambil RequestJudul di DosenRequestJudulController@index (Dosen User ID: ".Auth::id()."): " . $e->getMessage());
            return redirect()->route('dashboard') // Atau ke dashboard dosen
                             ->with('error', 'Gagal memuat daftar pengajuan judul. Silakan coba lagi nanti.');
        }

        return view('dosen.request_judul.index', compact('requests', 'filterStatus'));
    }

    // --- Method show(), edit(), update() akan kita bahas setelah index ini bekerja ---
    // show() akan menampilkan detail satu request
    // edit() akan menampilkan form untuk dosen mengubah status & memberi catatan
    // update() akan memproses perubahan status & catatan dari dosen

    public function show(RequestJudul $requestJudul)
    {
        // Otorisasi: Pastikan request ini memang ditujukan ke dosen yang login
        $userDosen = Auth::user();
        if (!$userDosen || !$userDosen->dosen || $requestJudul->dosen_tujuan_id !== $userDosen->dosen->id) {
            abort(403, 'Anda tidak berhak mengakses detail pengajuan ini.');
        }

        $requestJudul->load(['mahasiswa.user:id,name,email', 'mahasiswa.prodi:id,nama_prodi,fakultas']);
        return view('dosen.request_judul.show', compact('requestJudul'));
    }

    public function edit(RequestJudul $requestJudul)
    {
        // Otorisasi
        $userDosen = Auth::user();
        if (!$userDosen || !$userDosen->dosen || $requestJudul->dosen_tujuan_id !== $userDosen->dosen->id) {
            abort(403, 'Anda tidak berhak mengubah pengajuan ini.');
        }

        // Dosen hanya bisa memproses request yang masih pending (atau status lain yang relevan)
        if (!in_array($requestJudul->status, ['pending'])) { // Tambahkan status lain jika perlu
             return redirect()->route('dosen.request-judul.show', $requestJudul->id)
                              ->with('warning', 'Pengajuan judul ini tidak dapat diproses karena statusnya bukan \'pending\'.');
        }

        $requestJudul->load(['mahasiswa.user:id,name', 'mahasiswa.prodi:id,nama_prodi']);
        return view('dosen.request_judul.edit', compact('requestJudul'));
    }

    public function update(Request $request, RequestJudul $requestJudul)
    {
        // Otorisasi
        $userDosen = Auth::user();
        if (!$userDosen || !$userDosen->dosen || $requestJudul->dosen_tujuan_id !== $userDosen->dosen->id) {
            abort(403, 'Anda tidak berhak mengubah pengajuan ini.');
        }

        if (!in_array($requestJudul->status, ['pending'])) {
             return redirect()->route('dosen.request-judul.show', $requestJudul->id)
                              ->with('warning', 'Pengajuan judul ini tidak dapat diproses karena statusnya bukan \'pending\'.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'catatan_dosen' => 'nullable|string|max:1000',
        ]);

        try {
            $requestJudul->status = $validated['status'];
            $requestJudul->catatan_dosen = $validated['catatan_dosen'];
            // Jika status 'approved', mungkin ada logika tambahan:
            // - Mengupdate judul_proyek_akhir dan dosen_pembimbing_id di tabel mahasiswa
            // - Mengirim notifikasi ke mahasiswa
            if ($validated['status'] === 'approved') {
                $mahasiswa = $requestJudul->mahasiswa;
                if ($mahasiswa) {
                    $mahasiswa->judul_proyek_akhir = $requestJudul->judul_diajukan;
                    $mahasiswa->dosen_pembimbing_id = $requestJudul->dosen_tujuan_id; // Menyimpan ID Dosen, bukan User ID Dosen
                    // Atau jika Anda menyimpan user_id dosen di tabel mahasiswa:
                    // $mahasiswa->dosen_pembimbing_id = $userDosen->id;
                    $mahasiswa->status_proyek_akhir = 'bimbingan'; // Atau status lain yang sesuai
                    $mahasiswa->save();

                    // Nonaktifkan request judul lain yang pending dari mahasiswa ini
                    RequestJudul::where('mahasiswa_id', $mahasiswa->id)
                                ->where('id', '!=', $requestJudul->id)
                                ->where('status', 'pending')
                                ->update(['status' => 'cancelled_by_system']); // Atau status lain
                }
            }

            $requestJudul->save();

            // TODO: Notifikasi ke Mahasiswa mengenai status pengajuannya

            return redirect()->route('dosen.request-judul.index')
                             ->with('success', 'Status pengajuan judul berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Error update RequestJudul ID '.$requestJudul->id.' oleh Dosen User ID '.Auth::id().': ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui status pengajuan.');
        }
    }
}
