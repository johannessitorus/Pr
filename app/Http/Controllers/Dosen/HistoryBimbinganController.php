<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\HistoryBimbingan;
use App\Models\Dosen; // Untuk otorisasi dan data dosen
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule; // Untuk validasi enum
use Illuminate\Validation\ValidationException;

class HistoryBimbinganController extends Controller
{
    /**
     * Mengambil data dosen yang sedang login.
     */
    private function getAuthenticatedDosen()
    {
        $user = Auth::user();
        if (!$user || !$user->dosen) {
            Log::warning('User tidak memiliki profil dosen atau tidak login saat akses Dosen\HistoryBimbingan.', ['user_id' => $user ? $user->id : null]);
            return null;
        }
        return $user->dosen;
    }

    /**
     * Display a listing of the resource for the authenticated dosen.
     */
    public function index(Request $request)
    {
        $dosen = $this->getAuthenticatedDosen();
        if (!$dosen) {
            return redirect()->route('dashboard')->with('error', 'Data profil dosen Anda tidak ditemukan.');
        }

        try {
            $query = HistoryBimbingan::where('dosen_id', $dosen->id)
                                    ->with(['mahasiswa.user:id,name', 'requestBimbingan:id,topik_bimbingan'])
                                    ->orderBy('tanggal_bimbingan', 'desc');

            if ($request->filled('mahasiswa_filter')) {
                $query->where('mahasiswa_id', $request->input('mahasiswa_filter'));
            }

            $filterStatus = $request->input('status_filter');
            $validStatuses = ['terjadwal', 'hadir', 'selesai', 'tidak_hadir_mahasiswa', 'tidak_hadir_dosen', 'dibatalkan_mahasiswa', 'dibatalkan_dosen', 'dijadwalkan_ulang'];
            if ($filterStatus && in_array($filterStatus, $validStatuses)) {
                $query->where('status_kehadiran', $filterStatus);
            }

            $histories = $query->paginate(10)->withQueryString();

            // $mahasiswasBimbingan = $dosen->mahasiswas()->with('user:id,name')->get();

        } catch (\Exception $e) {
            Log::error("Error fetching HistoryBimbingan for Dosen ID {$dosen->id}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('dashboard')->with('error', 'Gagal memuat history bimbingan.');
        }

        $statusesForFilter = $validStatuses;
        // 'mahasiswasForFilter' => $mahasiswasBimbingan ?? null,

        return view('dosen.history_bimbingan.index', compact('histories', 'filterStatus', 'statusesForFilter'));
    }

    /**
     * Display the specified resource.
     */
    public function show(HistoryBimbingan $historyBimbingan)
    {
        $dosen = $this->getAuthenticatedDosen();
        if (!$dosen || $historyBimbingan->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak mengakses detail history bimbingan ini.');
        }

        $historyBimbingan->load(['mahasiswa.user:id,name,email', 'requestBimbingan']);

        return view('dosen.history_bimbingan.show', compact('historyBimbingan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HistoryBimbingan $historyBimbingan)
    {
        $dosen = $this->getAuthenticatedDosen();
        if (!$dosen || $historyBimbingan->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak mengubah history bimbingan ini.');
        }

        $historyBimbingan->load(['mahasiswa.user:id,name', 'requestBimbingan']);
        $availableStatuses = [
            'hadir' => 'Hadir',
            'selesai' => 'Selesai (dengan catatan)',
            'tidak_hadir_mahasiswa' => 'Tidak Hadir (Mahasiswa)',
            'tidak_hadir_dosen' => 'Tidak Hadir (Dosen)',
            'dibatalkan_dosen' => 'Dibatalkan oleh Dosen',
        ];

        return view('dosen.history_bimbingan.edit', compact('historyBimbingan', 'availableStatuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HistoryBimbingan $historyBimbingan)
    {
        $dosen = $this->getAuthenticatedDosen();
        if (!$dosen || $historyBimbingan->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak mengubah history bimbingan ini.');
        }

        $validUpdateStatuses = ['hadir', 'selesai', 'tidak_hadir_mahasiswa', 'tidak_hadir_dosen', 'dibatalkan_dosen'];

        $validatedData = $request->validate([
            'status_kehadiran' => ['required', Rule::in($validUpdateStatuses)],
            'catatan_dosen' => 'nullable|string|max:5000',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'status_kehadiran' => $validatedData['status_kehadiran'],
                'catatan_dosen' => $validatedData['catatan_dosen'] ?? $historyBimbingan->catatan_dosen,
            ];

            $historyBimbingan->update($updateData);

            // TODO: Kirim Notifikasi ke Mahasiswa
            // $historyBimbingan->mahasiswa->user->notify(new BimbinganUpdatedByDosen($historyBimbingan));

            DB::commit();
            return redirect()->route('dosen.history-bimbingan.show', $historyBimbingan->id)
                             ->with('success', 'Data bimbingan berhasil diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating HistoryBimbingan ID {$historyBimbingan->id} by Dosen ID {$dosen->id}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data bimbingan.');
        }
    }

    public function create()
    {
        abort(405, 'Method Not Allowed.');
    }

    public function store(Request $request)
    {
        abort(405, 'Method Not Allowed.');
    }

    public function destroy(HistoryBimbingan $historyBimbingan)
    {
        $dosen = $this->getAuthenticatedDosen();
        if (!$dosen || $historyBimbingan->dosen_id !== $dosen->id) {
            abort(403, 'Akses ditolak.');
        }
        Log::warning("Attempt to delete HistoryBimbingan ID {$historyBimbingan->id} by Dosen ID ".Auth::id());
        abort(405, 'Method Not Allowed.');
    }
}
