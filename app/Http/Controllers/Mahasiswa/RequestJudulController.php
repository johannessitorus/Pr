<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dosen;
use App\Models\RequestJudul; // Pastikan model RequestJudul ada
use App\Models\Mahasiswa;   // Jika perlu akses langsung ke model Mahasiswa

class RequestJudulController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            return redirect()->route('dashboard')->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        $requests = RequestJudul::where('mahasiswa_id', $mahasiswa->id)
                                ->with('dosenTujuan.user') // Eager load dosen dan user dosen
                                ->latest()
                                ->paginate(10);

        return view('mahasiswa.request_judul.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa || !$mahasiswa->prodi_id) {
            return redirect()->route('dashboard') // Di versi awal Anda, ini ke dashboard
                             ->with('error', 'Informasi program studi Anda tidak lengkap untuk mengajukan judul.');
        }

        $prodiIdMahasiswa = $mahasiswa->prodi_id;

        // Ambil dosen yang satu prodi dengan mahasiswa dan memiliki user terkait
        $calonDosenPembimbing = Dosen::where('prodi_id', $prodiIdMahasiswa)
                                    ->whereHas('user') // Hanya dosen yang punya user (akun aktif)
                                    ->with('user:id,name') // Eager load hanya id dan nama dari user dosen
                                    ->get()
                                    ->sortBy('user.name'); // Urutkan berdasarkan nama dosen

        if ($calonDosenPembimbing->isEmpty()) {
            return redirect()->route('mahasiswa.request-judul.index') // Redirect ke index jika tidak ada dosen
                             ->with('error', 'Tidak ada dosen pembimbing yang tersedia untuk program studi Anda saat ini.');
        }

        return view('mahasiswa.request_judul.create', compact('calonDosenPembimbing'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa || !$mahasiswa->prodi_id) {
            return redirect()->back()->withInput()->with('error', 'Informasi program studi Anda tidak lengkap.');
        }

        $validatedData = $request->validate([
            'judul_diajukan' => 'required|string|max:255|min:10',
            'deskripsi' => 'required|string|min:20',
            'dosen_tujuan_id' => 'required|exists:dosen,id',
        ]);

        // Verifikasi tambahan: Dosen yang dipilih harus satu prodi dengan mahasiswa
        $dosenDipilih = Dosen::find($validatedData['dosen_tujuan_id']);
        if (!$dosenDipilih || $dosenDipilih->prodi_id !== $mahasiswa->prodi_id) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Dosen yang Anda pilih tidak valid untuk program studi Anda.');
        }

        // Cek apakah mahasiswa sudah punya request judul yang statusnya 'pending' atau 'approved'
        $existingRequest = RequestJudul::where('mahasiswa_id', $mahasiswa->id)
                                        ->whereIn('status', ['pending', 'approved'])
                                        ->first();
        if ($existingRequest) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Anda sudah memiliki pengajuan judul yang sedang diproses atau sudah disetujui.');
        }

        $requestJudul = new RequestJudul();
        $requestJudul->mahasiswa_id = $mahasiswa->id;
        $requestJudul->dosen_tujuan_id = $validatedData['dosen_tujuan_id'];
        $requestJudul->judul_diajukan = $validatedData['judul_diajukan'];
        $requestJudul->deskripsi = $validatedData['deskripsi'];
        $requestJudul->status = 'pending'; // Status awal
        $requestJudul->save();

        // TODO: Implementasi Notifikasi ke Dosen
        // Contoh: $dosenDipilih->user->notify(new PengajuanJudulBaruNotification($requestJudul));

        // TODO: Implementasi Log Aktivitas
        // activity()->performedOn($requestJudul)->causedBy($user)->log('Mengajukan judul baru');

        return redirect()->route('mahasiswa.request-judul.index')
                         ->with('success', 'Pengajuan judul berhasil dikirim.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestJudul $requestJudul) // Route Model Binding
    {
        // Pastikan mahasiswa hanya bisa melihat request miliknya
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa || $requestJudul->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Akses ditolak.');
        }

        // Eager load relasi yang dibutuhkan
        $requestJudul->load('mahasiswa.user', 'mahasiswa.prodi', 'dosenTujuan.user');

        return view('mahasiswa.request_judul.show', compact('requestJudul'));
    }

     public function edit(RequestJudul $requestJudul) // Route Model Binding
    {
        $mahasiswa = Auth::user()->mahasiswa;

        // 1. Pastikan request judul ini milik mahasiswa yang login
        if (!$mahasiswa || $requestJudul->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Pastikan status request memungkinkan untuk diedit (misalnya 'pending' atau 'revisi')
        if (!in_array($requestJudul->status, ['pending', 'revisi_mahasiswa'])) { // 'revisi_mahasiswa' jika ada status khusus revisi oleh mhs
            return redirect()->route('mahasiswa.request-judul.show', $requestJudul->id)
                             ->with('error', 'Pengajuan judul ini tidak dapat diedit karena statusnya sudah ' . ucfirst($requestJudul->status) . '.');
        }

        // 3. Ambil daftar calon dosen pembimbing dari prodi mahasiswa (sama seperti di method create)
        $prodiIdMahasiswa = $mahasiswa->prodi_id;
        $calonDosenPembimbing = Dosen::where('prodi_id', $prodiIdMahasiswa)
                                    ->whereHas('user')
                                    ->with('user:id,name')
                                    ->get()
                                    ->sortBy('user.name');

        // Jika tidak ada dosen yang bisa dipilih (seharusnya jarang terjadi jika sudah pernah create)
        if ($calonDosenPembimbing->isEmpty() && $requestJudul->dosen_tujuan_id === null) { // Penambahan cek dosen_tujuan_id null untuk kasus edit
             return redirect()->route('mahasiswa.request-judul.show', $requestJudul->id)
                              ->with('error', 'Tidak ada dosen pembimbing yang tersedia untuk program studi Anda saat ini.');
        }

        return view('mahasiswa.request_judul.edit', compact('requestJudul', 'calonDosenPembimbing'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestJudul $requestJudul) // Route Model Binding
    {
        $mahasiswa = Auth::user()->mahasiswa;

        // 1. Pastikan request judul ini milik mahasiswa yang login
        if (!$mahasiswa || $requestJudul->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Pastikan status request memungkinkan untuk diedit
        if (!in_array($requestJudul->status, ['pending', 'revisi_mahasiswa'])) {
            return redirect()->route('mahasiswa.request-judul.show', $requestJudul->id)
                             ->with('error', 'Pengajuan judul ini tidak dapat diupdate karena statusnya sudah ' . ucfirst($requestJudul->status) . '.');
        }

        // 3. Validasi data
        $validatedData = $request->validate([
            'judul_diajukan' => 'required|string|max:255|min:10',
            'deskripsi' => 'required|string|min:20',
            'dosen_tujuan_id' => 'required|exists:dosen,id',
        ]);

        // 4. Verifikasi tambahan: Dosen yang dipilih harus satu prodi dengan mahasiswa
        $dosenDipilih = Dosen::find($validatedData['dosen_tujuan_id']);
        if (!$dosenDipilih || $dosenDipilih->prodi_id !== $mahasiswa->prodi_id) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Dosen yang Anda pilih tidak valid untuk program studi Anda.');
        }

        // 5. Update data request judul
        $requestJudul->judul_diajukan = $validatedData['judul_diajukan'];
        $requestJudul->deskripsi = $validatedData['deskripsi'];
        $requestJudul->dosen_tujuan_id = $validatedData['dosen_tujuan_id'];
        // Jika ada status 'revisi_mahasiswa', mungkin Anda ingin mengubahnya kembali ke 'pending' setelah diedit
        if ($requestJudul->status === 'revisi_mahasiswa') { // Pindahkan logika ini ke sini
            $requestJudul->status = 'pending';
        }
        $requestJudul->save();

        // TODO: Log Aktivitas
        // activity()->performedOn($requestJudul)->causedBy(Auth::user())->log('Memperbarui pengajuan judul');

        return redirect()->route('mahasiswa.request-judul.show', $requestJudul->id)
                         ->with('success', 'Pengajuan judul berhasil diperbarui.');
    }

    public function destroy(RequestJudul $requestJudul) // Route Model Binding
    {
        $mahasiswa = Auth::user()->mahasiswa;

        // 1. Pastikan request judul ini milik mahasiswa yang login
        if (!$mahasiswa || $requestJudul->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Pastikan status request memungkinkan untuk dihapus (hanya 'pending')
        if ($requestJudul->status !== 'pending') {
            return redirect()->route('mahasiswa.request-judul.show', $requestJudul->id)
                             ->with('error', 'Pengajuan judul ini tidak dapat dibatalkan karena statusnya bukan pending.');
        }

        $requestJudul->delete();

        // TODO: Log Aktivitas
        // activity()->causedBy(Auth::user())->log('Membatalkan pengajuan judul: ' . $requestJudul->judul_diajukan);

        return redirect()->route('mahasiswa.request-judul.index')
                         ->with('success', 'Pengajuan judul berhasil dibatalkan.');
    }
}
