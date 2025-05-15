<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\HistoryBimbingan;
use App\Models\Mahasiswa; // Untuk mendapatkan data mahasiswa
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HistoryBimbinganController extends Controller
{
    /**
     * Mengambil data mahasiswa yang sedang login.
     */
    private function getMahasiswaData()
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa) {
            Log::warning('User tidak memiliki profil mahasiswa atau tidak login saat akses HistoryBimbingan.', ['user_id' => $user ? $user->id : null]);
            return null;
        }
        return $user->mahasiswa;
    }

    /**
     * Display a listing of the resource for the authenticated mahasiswa.
     */
    public function index(Request $request)
    {
        $mahasiswa = $this->getMahasiswaData();
        if (!$mahasiswa) {
            return redirect()->route('dashboard')->with('error', 'Data profil mahasiswa Anda tidak ditemukan.');
        }

        try {
            $query = HistoryBimbingan::where('mahasiswa_id', $mahasiswa->id)
                                    ->with(['dosen.user:id,name']) // Eager load dosen dan nama user dosen
                                    ->orderBy('tanggal_bimbingan', 'desc');

            // Filter berdasarkan status jika ada input dari request
            $filterStatus = $request->input('status_filter');
            // Daftar status yang valid untuk filter. Sesuaikan dengan enum Anda.
            $validStatuses = ['terjadwal', 'hadir', 'selesai', 'tidak_hadir_mahasiswa', 'tidak_hadir_dosen', 'dibatalkan_mahasiswa', 'dibatalkan_dosen', 'dijadwalkan_ulang'];

            if ($filterStatus && in_array($filterStatus, $validStatuses)) {
                $query->where('status_kehadiran', $filterStatus);
            }

            $histories = $query->paginate(10)->withQueryString(); // withQueryString agar filter tetap ada saat paginasi

        } catch (\Exception $e) {
            Log::error("Error fetching HistoryBimbingan for Mahasiswa ID {$mahasiswa->id}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('dashboard')->with('error', 'Gagal memuat history bimbingan.');
        }

        // Kirim daftar status yang valid ke view untuk dropdown filter
        $statusesForFilter = $validStatuses;


        return view('mahasiswa.history_bimbingan.index', compact('histories', 'filterStatus', 'statusesForFilter'));
    }

    /**
     * Display the specified resource.
     */
    public function show(HistoryBimbingan $historyBimbingan)
    {
        $mahasiswa = $this->getMahasiswaData();
        // Otorisasi: Pastikan history bimbingan ini milik mahasiswa yang login
        if (!$mahasiswa || $historyBimbingan->mahasiswa_id !== $mahasiswa->id) {
            // Sebaiknya gunakan Policy: $this->authorize('view', $historyBimbingan);
            abort(403, 'Anda tidak berhak mengakses detail history bimbingan ini.');
        }

        // Load relasi yang mungkin dibutuhkan di view show
        $historyBimbingan->load(['dosen.user:id,name,email', 'requestBimbingan']);

        return view('mahasiswa.history_bimbingan.show', compact('historyBimbingan'));
    }


    /**
     * Show the form for editing the specified resource.
     * Mungkin digunakan jika mahasiswa bisa mengupdate sesuatu (misal: upload file, catatan pribadi, atau membatalkan).
     */
    public function edit(HistoryBimbingan $historyBimbingan)
    {
        $mahasiswa = $this->getMahasiswaData();
        if (!$mahasiswa || $historyBimbingan->mahasiswa_id !== $mahasiswa->id) {
            // $this->authorize('update', $historyBimbingan);
            abort(403, 'Akses ditolak.');
        }

        // Contoh: Mahasiswa hanya bisa edit jika statusnya 'terjadwal' untuk membatalkan
        // atau 'hadir'/'selesai' untuk menambah catatan/file
        // Logika ini sangat bergantung pada fitur yang Anda inginkan
        if (!in_array($historyBimbingan->status_kehadiran, ['terjadwal', 'hadir', 'selesai'])) {
            return redirect()->route('mahasiswa.history-bimbingan.show', $historyBimbingan->id)
                             ->with('warning', 'History bimbingan ini tidak dapat diubah pada status saat ini.');
        }

        $historyBimbingan->load(['dosen.user:id,name']);
        return view('mahasiswa.history_bimbingan.edit', compact('historyBimbingan'));
    }

    /**
     * Update the specified resource in storage.
     * Contoh: Mahasiswa mengupload file catatan atau membatalkan bimbingan.
     */
    public function update(Request $request, HistoryBimbingan $historyBimbingan)
    {
        $mahasiswa = $this->getMahasiswaData();
        if (!$mahasiswa || $historyBimbingan->mahasiswa_id !== $mahasiswa->id) {
            // $this->authorize('update', $historyBimbingan);
            abort(403, 'Akses ditolak.');
        }

        // Contoh validasi jika mahasiswa bisa membatalkan atau menambah catatan
        // Sesuaikan validasi dengan field yang bisa diubah mahasiswa
        $validatedData = $request->validate([
            'catatan_mahasiswa_update' => 'nullable|string|max:2000', // Jika mahasiswa bisa update catatannya
            'file_catatan_mahasiswa' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // Max 5MB
            'aksi_pembatalan' => 'nullable|in:cancel', // Jika ada tombol khusus untuk batal
        ]);

        DB::beginTransaction();
        try {
            $updateData = [];

            if ($request->has('aksi_pembatalan') && $request->input('aksi_pembatalan') === 'cancel') {
                if ($historyBimbingan->status_kehadiran === 'terjadwal') { // Hanya bisa batal jika masih terjadwal
                    $updateData['status_kehadiran'] = 'dibatalkan_mahasiswa';
                    // Tambahkan field alasan pembatalan jika perlu
                    // $updateData['alasan_pembatalan'] = $request->input('alasan_pembatalan');
                } else {
                    return redirect()->back()->with('error', 'Bimbingan tidak dapat dibatalkan pada status saat ini.');
                }
            }

            if (isset($validatedData['catatan_mahasiswa_update'])) {
                // Pertimbangkan apakah ini menimpa atau menambah catatan
                // Mungkin lebih baik field terpisah: 'resume_mahasiswa' atau 'feedback_mahasiswa_setelah_sesi'
                $updateData['catatan_mahasiswa'] = $validatedData['catatan_mahasiswa_update'];
            }

            if ($request->hasFile('file_catatan_mahasiswa')) {
                // Hapus file lama jika ada
                // if ($historyBimbingan->file_catatan_mahasiswa) { Storage::disk('public')->delete($historyBimbingan->file_catatan_mahasiswa); }
                // $filePath = $request->file('file_catatan_mahasiswa')->store('history_bimbingan/catatan_mahasiswa', 'public');
                // $updateData['file_catatan_mahasiswa'] = $filePath;
                // Logika upload file belum lengkap, hanya contoh
                Log::info('Mahasiswa mencoba upload file catatan untuk history ID: ' . $historyBimbingan->id); // Placeholder
            }

            if (!empty($updateData)) {
                $historyBimbingan->update($updateData);
            } else {
                 // Jika tidak ada data yang diupdate, tidak perlu query ke DB
                 DB::rollBack(); // Batalkan transaksi jika dimulai tapi tidak ada perubahan
                 return redirect()->route('mahasiswa.history-bimbingan.show', $historyBimbingan->id)
                                 ->with('info', 'Tidak ada perubahan yang disimpan.');
            }

            // TODO: Notifikasi ke Dosen jika ada pembatalan atau update signifikan dari mahasiswa

            DB::commit();
            return redirect()->route('mahasiswa.history-bimbingan.show', $historyBimbingan->id)
                             ->with('success', 'Informasi bimbingan berhasil diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating HistoryBimbingan ID {$historyBimbingan->id} by Mahasiswa ID {$mahasiswa->id}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui informasi bimbingan.');
        }
    }


    /**
     * Mahasiswa biasanya tidak menghapus history bimbingan.
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(405, 'Method Not Allowed. Mahasiswa tidak dapat membuat history bimbingan secara manual.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(405, 'Method Not Allowed. Mahasiswa tidak dapat membuat history bimbingan secara manual.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HistoryBimbingan $historyBimbingan)
    {
        // Otorisasi
        $mahasiswa = $this->getMahasiswaData();
        if (!$mahasiswa || $historyBimbingan->mahasiswa_id !== $mahasiswa->id) {
            // $this->authorize('delete', $historyBimbingan);
            abort(403, 'Akses ditolak.');
        }

        // Pertimbangkan apakah mahasiswa boleh menghapus (biasanya tidak)
        // Jika ya, mungkin hanya jika status tertentu
        // $historyBimbingan->delete();
        // return redirect()->route('mahasiswa.history-bimbingan.index')->with('success', 'History bimbingan dihapus.');
        Log::warning("Attempt to delete HistoryBimbingan ID {$historyBimbingan->id} by Mahasiswa ID ".Auth::id());
        abort(405, 'Method Not Allowed. Mahasiswa tidak diizinkan menghapus history bimbingan.');
    }
}
