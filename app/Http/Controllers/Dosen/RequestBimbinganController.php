<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\RequestBimbingan;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\HistoryBimbingan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestBimbinganController extends Controller
{
    // ... (method getAuthenticatedDosen, index, show, edit tetap sama seperti versi final sebelumnya) ...

    private function getAuthenticatedDosen()
    {
        $user = Auth::user();
        if (!$user || !$user->dosen) {
            return null;
        }
        return $user->dosen;
    }

    public function index(Request $request)
    {
        $dosen = $this->getAuthenticatedDosen();
        if (!$dosen) {
            Log::warning('Dosen\RequestBimbinganController@index: Data profil dosen tidak ditemukan untuk User ID: ' . Auth::id());
            return redirect()->route('dashboard')->with('error', 'Data profil dosen Anda tidak ditemukan.');
        }

        try {
            $query = RequestBimbingan::where('dosen_id', $dosen->id);
            $filterStatus = $request->input('status', 'pending');
            if (!empty($filterStatus) && in_array($filterStatus, ['pending', 'approved', 'rejected', 'rescheduled', 'completed', 'cancelled'])) {
                $query->where('status_request', $filterStatus);
            }
            $requests = $query->with(['mahasiswa.user:id,name', 'mahasiswa.prodi:id,nama_prodi'])
                             ->orderBy('created_at', 'desc')
                             ->paginate(10);
        } catch (\Throwable $e) {
            Log::error("Dosen\RequestBimbinganController@index (Dosen ID: ".$dosen->id."): Error saat mengambil RequestBimbingan - " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Gagal memuat daftar request bimbingan. Silakan coba lagi.');
        }
        return view('dosen.request_bimbingan.index', compact('requests', 'filterStatus'));
    }

    public function show(RequestBimbingan $requestBimbingan)
    {
        $dosen = $this->getAuthenticatedDosen();
        if (!$dosen || $requestBimbingan->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak mengakses detail request ini.');
        }
        $requestBimbingan->load(['mahasiswa.user:id,name,email', 'mahasiswa.prodi:id,nama_prodi', 'dosen.user:id,name']);
        return view('dosen.request_bimbingan.show', compact('requestBimbingan'));
    }

    public function edit(RequestBimbingan $requestBimbingan)
    {
        $dosen = $this->getAuthenticatedDosen();
        if (!$dosen || $requestBimbingan->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak memproses request ini.');
        }
        if ($requestBimbingan->status_request !== 'pending') {
             return redirect()->route('dosen.request-bimbingan.show', $requestBimbingan->id)
                              ->with('warning', 'Request bimbingan ini tidak dapat diproses karena statusnya bukan "pending".');
        }
        $requestBimbingan->load(['mahasiswa.user:id,name', 'mahasiswa.prodi:id,nama_prodi']);
        return view('dosen.request_bimbingan.edit', compact('requestBimbingan'));
    }

    /**
     * Update the specified resource in storage.
     * Dosen memperbarui status request bimbingan dan membuat history jika disetujui.
     */
    public function update(Request $request, RequestBimbingan $requestBimbingan)
    {
        $dosen = $this->getAuthenticatedDosen();
        if (!$dosen || $requestBimbingan->dosen_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak memproses request ini.');
        }

        if ($requestBimbingan->status_request !== 'pending') {
             return redirect()->route('dosen.request-bimbingan.show', $requestBimbingan->id)
                              ->with('warning', 'Request bimbingan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'status_request' => 'required|in:approved,rejected,rescheduled',
            'catatan_dosen' => 'nullable|string|max:1000',
            'tanggal_dosen' => 'nullable|required_if:status_request,rescheduled|date|after_or_equal:today',
            'jam_dosen' => 'nullable|required_if:status_request,rescheduled|date_format:H:i',
        ]);

        DB::beginTransaction();
        try {
            $requestBimbingan->status_request = $validated['status_request'];
            $requestBimbingan->catatan_dosen = $validated['catatan_dosen'];

            if ($validated['status_request'] === 'rescheduled') {
                $requestBimbingan->tanggal_dosen = $validated['tanggal_dosen'];
                $requestBimbingan->jam_dosen = $validated['jam_dosen'];
            } elseif ($validated['status_request'] === 'approved') {
                $requestBimbingan->tanggal_dosen = $requestBimbingan->tanggal_usulan;
                $requestBimbingan->jam_dosen = $requestBimbingan->jam_usulan;
            }
            $requestBimbingan->save();

            if ($validated['status_request'] === 'approved') {
                if ($requestBimbingan->tanggal_dosen && $requestBimbingan->jam_dosen) {
                    $tanggalBimbinganFinal = null;
                    $stringUntukDiParse = '';

                    try {
                        $tanggalBagianDariTanggalDosen = '';
                        if ($requestBimbingan->tanggal_dosen instanceof \Carbon\Carbon) {
                            $tanggalBagianDariTanggalDosen = $requestBimbingan->tanggal_dosen->format('Y-m-d');
                        } else {
                            $tanggalBagianDariTanggalDosen = Carbon::parse((string) $requestBimbingan->tanggal_dosen)->format('Y-m-d');
                        }
                        $jamBagian = (string) $requestBimbingan->jam_dosen;
                        $stringUntukDiParse = $tanggalBagianDariTanggalDosen . ' ' . $jamBagian;
                        Log::debug("Dosen\RequestBimbinganController@update: String yang akan diparse Carbon untuk history: " . $stringUntukDiParse);
                        $tanggalBimbinganFinal = Carbon::parse($stringUntukDiParse)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        $originalTanggal = $requestBimbingan->getRawOriginal('tanggal_dosen') ?? $requestBimbingan->tanggal_dosen;
                        $originalJam = $requestBimbingan->getRawOriginal('jam_dosen') ?? $requestBimbingan->jam_dosen;
                        Log::error("Dosen\RequestBimbinganController@update (Request ID: {$requestBimbingan->id}): Error parsing tanggal/jam untuk history - " . $e->getMessage(), [
                            'original_tanggal_dosen' => $originalTanggal, 'original_jam_dosen' => $originalJam,
                            'string_coba_diparse' => $stringUntukDiParse, 'trace' => $e->getTraceAsString()
                        ]);
                        DB::rollBack();
                        return redirect()->back()->withInput()->with('error', 'Format tanggal atau jam bimbingan tidak valid untuk pembuatan histori. Silakan periksa input atau hubungi administrator.');
                    }

                    $pertemuanKe = HistoryBimbingan::where('mahasiswa_id', $requestBimbingan->mahasiswa_id)
                                                ->where('dosen_id', $requestBimbingan->dosen_id)
                                                ->count() + 1;

                    HistoryBimbingan::create([
                        'mahasiswa_id' => $requestBimbingan->mahasiswa_id,
                        'dosen_id' => $requestBimbingan->dosen_id,
                        'request_bimbingan_id' => $requestBimbingan->id,
                        'tanggal_bimbingan' => $tanggalBimbinganFinal,
                        'topik' => $requestBimbingan->topik_bimbingan,
                        'catatan_mahasiswa' => null,
                        'catatan_dosen' => null,
                        'pertemuan_ke' => $pertemuanKe,
                        'status_kehadiran' => 'hadir', // DISESUAIKAN DENGAN DATABASE YANG ADA
                    ]);
                    Log::info("Dosen\RequestBimbinganController@update: History bimbingan berhasil dibuat untuk Request ID: {$requestBimbingan->id}, Pertemuan ke: {$pertemuanKe}, Status Kehadiran Awal: hadir");
                } else {
                    Log::warning("Dosen\RequestBimbinganController@update (Request ID: {$requestBimbingan->id}): Tanggal atau jam bimbingan tidak lengkap. History tidak dibuat.");
                }
            }

            DB::commit();
            return redirect()->route('dosen.request-bimbingan.index')
                             ->with('success', 'Status request bimbingan berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning("Dosen\RequestBimbinganController@update (Request ID: {$requestBimbingan->id}): Validasi gagal - " . $e->getMessage(), $e->errors());
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Dosen\RequestBimbinganController@update (Request ID: {$requestBimbingan->id}): Error umum - " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui status request bimbingan: ' . $e->getMessage());
        }
    }

    public function destroy(RequestBimbingan $requestBimbingan)
    {
        abort(405, 'Method Not Allowed');
    }

    public function create()
    {
        abort(405, 'Method Not Allowed');
    }

    public function store(Request $request)
    {
        abort(405, 'Method Not Allowed');
    }
}
