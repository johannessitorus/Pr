<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\RequestBimbingan;
// use App\Models\Dosen; // Tidak secara langsung digunakan di store, tapi ok untuk method lain
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Untuk validasi tanggal
use Illuminate\Validation\ValidationException; // Untuk menangani error validasi secara eksplisit jika perlu

class RequestBimbinganController extends Controller
{
    /**
     * Mengambil data mahasiswa yang sedang login.
     */
    private function getMahasiswaData()
    {
        $user = Auth::user();
        // Pastikan user adalah mahasiswa dan memiliki relasi mahasiswa
        if (!$user || !$user->relationLoaded('mahasiswa') || !$user->mahasiswa) {
            // Coba load relasi jika belum ada, ini bisa terjadi jika tidak di-eager load sebelumnya
            if ($user && $user->loadMissing('mahasiswa')->mahasiswa) {
                 // Do nothing, $user->mahasiswa is now populated
            } else {
                Log::warning('User tidak memiliki profil mahasiswa atau tidak login.', ['user_id' => $user ? $user->id : null]);
                return null;
            }
        }
        return $user->mahasiswa;
    }

    public function index()
    {
        $mahasiswa = $this->getMahasiswaData();
        if (!$mahasiswa) {
            return redirect()->route('dashboard')->with('error', 'Data profil mahasiswa tidak ditemukan.');
        }

        if (!$mahasiswa->dosen_pembimbing_id || !$mahasiswa->dosenPembimbing) {
             return redirect()->route('dashboard')
                             ->with('warning', 'Anda belum memiliki dosen pembimbing. Fitur request bimbingan belum tersedia.');
        }

        $requests = RequestBimbingan::where('mahasiswa_id', $mahasiswa->id)
                                    ->with('dosen.user:id,name')
                                    ->orderBy('created_at', 'desc')
                                    ->paginate(10);

        return view('mahasiswa.request_bimbingan.index', compact('requests'));
    }

    public function create()
    {
        $mahasiswa = $this->getMahasiswaData();
        if (!$mahasiswa) {
            return redirect()->route('dashboard')->with('error', 'Data profil mahasiswa tidak ditemukan.');
        }

        if (!$mahasiswa->dosen_pembimbing_id || !$mahasiswa->dosenPembimbing) {
             return redirect()->route('mahasiswa.request-bimbingan.index') // Arahkan ke index request bimbingan saja
                             ->with('warning', 'Dosen pembimbing Anda belum ditetapkan. Anda belum bisa mengajukan bimbingan.');
        }

        $dosenPembimbing = $mahasiswa->dosenPembimbing()->with('user:id,name')->firstOrFail();

        return view('mahasiswa.request_bimbingan.create', compact('dosenPembimbing'));
    }

    public function store(Request $request)
    {
        $mahasiswa = $this->getMahasiswaData();

        // Validasi awal data mahasiswa dan dosen pembimbing
        if (!$mahasiswa) {
            Log::warning('Percobaan store RequestBimbingan tanpa data mahasiswa.', ['user_id' => Auth::id()]);
            return redirect()->back()->withInput()->with('error', 'Data profil mahasiswa tidak ditemukan.');
        }
        if (!$mahasiswa->dosen_pembimbing_id) {
            Log::warning('Percobaan store RequestBimbingan oleh mahasiswa tanpa dosen pembimbing.', ['mahasiswa_id' => $mahasiswa->id]);
            return redirect()->back()->withInput()->with('error', 'Dosen pembimbing Anda belum ditetapkan. Tidak dapat mengajukan bimbingan.');
        }

        // Validasi input form
        // Pastikan nama field di validasi SAMA PERSIS dengan atribut 'name' di form HTML Anda
        $validatedData = $request->validate([
            'tanggal_usulan' => 'required|date|after_or_equal:today',
            'jam_usulan' => 'required|date_format:H:i', // Validasi format jam (HH:MM 24 jam)
            'topik_bimbingan' => 'required|string|min:10|max:500',
            'lokasi_usulan' => 'nullable|string|max:255',
            'catatan_mahasiswa' => 'nullable|string|max:1000', // Jika nama input di form adalah 'catatan_tambahan', ganti ini
        ]);

        try {
            RequestBimbingan::create([
                'mahasiswa_id' => $mahasiswa->id,
                'dosen_id' => $mahasiswa->dosen_pembimbing_id,
                'tanggal_usulan' => $validatedData['tanggal_usulan'],
                'jam_usulan' => $validatedData['jam_usulan'],
                'topik_bimbingan' => $validatedData['topik_bimbingan'],
                'lokasi_usulan' => $validatedData['lokasi_usulan'] ?? null, // Gunakan null jika tidak ada input
                'catatan_mahasiswa' => $validatedData['catatan_mahasiswa'] ?? null, // Sesuaikan dengan nama field di Model dan DB
                'status_request' => 'pending', // Status default untuk pengajuan baru
            ]);

            return redirect()->route('mahasiswa.request-bimbingan.index')
                             ->with('success', 'Pengajuan bimbingan berhasil dikirim.');

        } catch (ValidationException $e) {
            // Laravel biasanya menangani ini secara otomatis dengan redirect back with errors.
            // Tapi jika ingin custom logging atau penanganan:
            Log::warning('Validation error saat store RequestBimbingan:', [
                'mahasiswa_id' => $mahasiswa->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            // Redirect back with errors and input (ini perilaku default Laravel)
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Tangkap semua jenis exception lain
            Log::error('Error store RequestBimbingan (Mahasiswa ID: '.$mahasiswa->id.'): ' . $e->getMessage(), [
                'exception' => $e, // Log seluruh objek exception untuk detail
                'trace' => $e->getTraceAsString() // Stack trace
            ]);
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan pada sistem saat mengirim pengajuan bimbingan. Silakan coba lagi nanti.');
        }
    }

    public function show(RequestBimbingan $requestBimbingan)
    {
        $mahasiswa = $this->getMahasiswaData();
        if (!$mahasiswa || $requestBimbingan->mahasiswa_id !== $mahasiswa->id) {
            // Sebaiknya gunakan Policy untuk otorisasi yang lebih bersih
            // $this->authorize('view', $requestBimbingan);
            abort(403, 'Akses ditolak.');
        }

        $requestBimbingan->load(['dosen.user:id,name', 'mahasiswa.user:id,name']);
        return view('mahasiswa.request_bimbingan.show', compact('requestBimbingan'));
    }

    public function edit(RequestBimbingan $requestBimbingan)
    {
        $mahasiswa = $this->getMahasiswaData();
        if (!$mahasiswa || $requestBimbingan->mahasiswa_id !== $mahasiswa->id) {
            // $this->authorize('update', $requestBimbingan);
            abort(403, 'Akses ditolak.');
        }
        if ($requestBimbingan->status_request !== 'pending') {
            return redirect()->route('mahasiswa.request-bimbingan.show', $requestBimbingan->id)
                             ->with('warning', 'Pengajuan ini tidak dapat diedit karena statusnya bukan "pending".');
        }

        $dosenPembimbing = $mahasiswa->dosenPembimbing()->with('user:id,name')->firstOrFail();

        return view('mahasiswa.request_bimbingan.edit', compact('requestBimbingan', 'dosenPembimbing'));
    }

    public function update(Request $request, RequestBimbingan $requestBimbingan)
    {
        $mahasiswa = $this->getMahasiswaData();
        if (!$mahasiswa || $requestBimbingan->mahasiswa_id !== $mahasiswa->id) {
            // $this->authorize('update', $requestBimbingan);
            abort(403, 'Akses ditolak.');
        }
        if ($requestBimbingan->status_request !== 'pending') {
            return redirect()->route('mahasiswa.request-bimbingan.show', $requestBimbingan->id)
                             ->with('warning', 'Pengajuan ini tidak dapat diupdate karena statusnya bukan "pending".');
        }

        $validatedData = $request->validate([
            'tanggal_usulan' => 'required|date|after_or_equal:today',
            'jam_usulan' => 'required|date_format:H:i',
            'topik_bimbingan' => 'required|string|min:10|max:500',
            'lokasi_usulan' => 'nullable|string|max:255',
            'catatan_mahasiswa' => 'nullable|string|max:1000', // Sesuaikan nama ini
        ]);

        try {
            // Hanya update field yang divalidasi, status_request tidak diubah di sini
            $requestBimbingan->update($validatedData);
            return redirect()->route('mahasiswa.request-bimbingan.show', $requestBimbingan->id)
                             ->with('success', 'Pengajuan bimbingan berhasil diperbarui.');
        } catch (ValidationException $e) {
            Log::warning('Validation error saat update RequestBimbingan ID '.$requestBimbingan->id.':', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error update RequestBimbingan ID '.$requestBimbingan->id.': ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengajuan bimbingan. Silakan coba lagi nanti.');
        }
    }

    public function destroy(RequestBimbingan $requestBimbingan)
    {
        $mahasiswa = $this->getMahasiswaData();
        if (!$mahasiswa || $requestBimbingan->mahasiswa_id !== $mahasiswa->id) {
            // $this->authorize('delete', $requestBimbingan);
            abort(403, 'Akses ditolak.');
        }
        if ($requestBimbingan->status_request !== 'pending') {
            return redirect()->route('mahasiswa.request-bimbingan.show', $requestBimbingan->id)
                             ->with('warning', 'Pengajuan ini tidak dapat dibatalkan karena statusnya bukan "pending".');
        }

        try {
            $requestBimbingan->delete();
            return redirect()->route('mahasiswa.request-bimbingan.index')
                             ->with('success', 'Pengajuan bimbingan berhasil dibatalkan.');
        } catch (\Exception $e) {
            Log::error('Error delete RequestBimbingan ID '.$requestBimbingan->id.': ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal membatalkan pengajuan bimbingan. Silakan coba lagi nanti.');
        }
    }
}
