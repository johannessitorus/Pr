<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\DokumenProyekAkhir;
use App\Models\JenisDokumen;       // Diperlukan untuk dropdown di form create
use App\Models\Mahasiswa;         // Diperlukan untuk mendapatkan ID mahasiswa yang login
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;   // Untuk logging error
use Illuminate\Support\Str;           // Untuk membuat nama file unik

class DokumenController extends Controller
{
    /**
     * Display a listing of the submitted documents for the authenticated mahasiswa.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa) { // Pastikan user adalah mahasiswa dan memiliki data mahasiswa terkait
            return redirect()->route('dashboard') // Atau rute login/dashboard mahasiswa yang sesuai
                             ->with('error', 'Data mahasiswa tidak ditemukan. Silakan hubungi admin.');
        }

        $mahasiswa = $user->mahasiswa;
        $dokumens = DokumenProyekAkhir::where('mahasiswa_id', $mahasiswa->id)
                        ->with('jenisDokumen') // Eager load relasi jenis dokumen
                        ->latest() // Urutkan berdasarkan yang terbaru (created_at)
                        ->paginate(10);

        return view('mahasiswa.dokumen.index', compact('dokumens', 'mahasiswa'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jenisDokumens = JenisDokumen::orderBy('nama_jenis')->get();
        if ($jenisDokumens->isEmpty()) {
            return redirect()->route('mahasiswa.dokumen.index') // Sesuaikan nama rute jika berbeda
                             ->with('warning', 'Tidak ada jenis dokumen yang tersedia untuk diupload. Hubungi admin.');
        }
        return view('mahasiswa.dokumen.create', compact('jenisDokumens'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
            'file_dokumen' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,zip,rar|max:10240', // Max 10MB, sesuaikan ekstensi dan ukuran
            'catatan_mahasiswa' => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();
        if (!$user || !$user->mahasiswa) {
            return back()->with('error', 'Gagal submit dokumen. Data mahasiswa tidak ditemukan.')->withInput();
        }
        $mahasiswa = $user->mahasiswa;

        if ($request->hasFile('file_dokumen')) {
            $file = $request->file('file_dokumen');
            $namaFileAsli = $file->getClientOriginalName();
            $ekstensiFile = $file->getClientOriginalExtension();

            // Buat nama file unik: nim_jenisdokumen_timestamp_random.ekstensi
            $jenisDokumenSlug = Str::slug(JenisDokumen::find($request->jenis_dokumen_id)->nama_jenis ?? 'dokumen');
            $namaFileUnik = Str::slug($mahasiswa->nim . '_' . $jenisDokumenSlug . '_' . time() . '_' . Str::random(5)) . '.' . $ekstensiFile;

            // Tentukan path penyimpanan (misal: 'dokumen_mahasiswa/nim_mahasiswa/')
            // Pastikan direktori ini writable oleh server web.
            $pathPenyimpanan = 'dokumen_mahasiswa/' . $mahasiswa->nim;
            $filePath = $file->storeAs($pathPenyimpanan, $namaFileUnik, 'public'); // Simpan di storage/app/public/

            if ($filePath) {
                // Cek versi terakhir untuk jenis dokumen ini oleh mahasiswa ini
                $versiTerakhir = DokumenProyekAkhir::where('mahasiswa_id', $mahasiswa->id)
                                    ->where('jenis_dokumen_id', $request->jenis_dokumen_id)
                                    ->max('versi');
                $versiBaru = $versiTerakhir ? $versiTerakhir + 1 : 1;

                DokumenProyekAkhir::create([
                'mahasiswa_id' => $mahasiswa->id,
                'jenis_dokumen_id' => $request->jenis_dokumen_id,
                'nama_file_asli' => $namaFileAsli,         // <--- ini
                'nama_file_unik' => $namaFileUnik,         // <--- ini
                'file_path' => $filePath,
                'ekstensi_file' => $ekstensiFile,       // <--- ini
                'ukuran_file' => $file->getSize(),        // <--- ini
                'versi' => $versiBaru,
                'catatan_mahasiswa' => $request->catatan_mahasiswa,
                'status_review' => 'pending',
            ]);

                return redirect()->route('mahasiswa.dokumen.index') // Sesuaikan nama rute jika berbeda
                                 ->with('success', 'Dokumen berhasil disubmit.');
            } else {
                Log::error("Gagal menyimpan file untuk mahasiswa ID: {$mahasiswa->id}. Path: {$pathPenyimpanan}, Nama Unik: {$namaFileUnik}");
                return back()->with('error', 'Gagal menyimpan file. Server tidak dapat menulis file. Silakan coba lagi atau hubungi admin.')->withInput();
            }
        }
        return back()->with('error', 'File dokumen tidak ditemukan atau tidak valid.')->withInput();
    }

    /**
     * Display the specified resource.
     * Parameter $dokumenProyekAkhir akan otomatis di-resolve oleh Route Model Binding.
     */
    public function show(DokumenProyekAkhir $dokumenProyekAkhir)
    {
        // Pastikan mahasiswa hanya bisa melihat dokumennya sendiri
        $user = Auth::user();
        if (!$user || !$user->mahasiswa || $dokumenProyekAkhir->mahasiswa_id !== $user->mahasiswa->id) {
            abort(403, 'ANDA TIDAK DIIZINKAN MELIHAT DOKUMEN INI.');
        }

        $dokumenProyekAkhir->load(['jenisDokumen', 'reviewer.user']); // Eager load relasi
        return view('mahasiswa.dokumen.show', compact('dokumenProyekAkhir'));
    }

    /**
     * Show the form for editing the specified resource.
     * Biasanya tidak digunakan oleh mahasiswa untuk dokumen yang sudah disubmit,
     * kecuali untuk alur revisi yang spesifik.
     */
    public function edit(DokumenProyekAkhir $dokumenProyekAkhir)
    {
        // Implementasi jika mahasiswa boleh mengedit detail tertentu (bukan file)
        // atau jika ini bagian dari alur revisi.
        // Contoh:
        // $user = Auth::user();
        // if (!$user || !$user->mahasiswa || $dokumenProyekAkhir->mahasiswa_id !== $user->mahasiswa->id) {
        //     abort(403, 'ANDA TIDAK DIIZINKAN MENGEDIT DOKUMEN INI.');
        // }
        // if ($dokumenProyekAkhir->status_review !== 'revision_needed') {
        //      return redirect()->route('mahasiswa.dokumen.show', $dokumenProyekAkhir->id)
        //                       ->with('error', 'Hanya dokumen yang membutuhkan revisi yang dapat diedit.');
        // }
        // $jenisDokumens = JenisDokumen::orderBy('nama_jenis')->get();
        // return view('mahasiswa.dokumen.edit', compact('dokumenProyekAkhir', 'jenisDokumens'));
        return redirect()->route('mahasiswa.dokumen.show', $dokumenProyekAkhir->id);
    }

    /**
     * Update the specified resource in storage.
     * Biasanya tidak digunakan oleh mahasiswa untuk dokumen yang sudah disubmit,
     * kecuali untuk alur revisi.
     */
    public function update(Request $request, DokumenProyekAkhir $dokumenProyekAkhir)
    {
        // Implementasi jika mahasiswa boleh mengupdate (misal, upload revisi)
        // Validasi, proses file baru, update record, dll.
        // Penting untuk menangani versi file jika ini adalah revisi.
        return redirect()->route('mahasiswa.dokumen.show', $dokumenProyekAkhir->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DokumenProyekAkhir $dokumenProyekAkhir)
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa || $dokumenProyekAkhir->mahasiswa_id !== $user->mahasiswa->id) {
            return redirect()->route('mahasiswa.dokumen.index')
                             ->with('error', 'Anda tidak memiliki izin untuk menghapus dokumen ini.');
        }

        // Logika tambahan: Mahasiswa hanya boleh menghapus dokumen jika statusnya masih 'pending'
        // atau 'revision_needed'. Sesuaikan dengan kebutuhan.
        if (!in_array($dokumenProyekAkhir->status_review, ['pending', 'revision_needed'])) {
            return redirect()->route('mahasiswa.dokumen.index')
                             ->with('error', 'Dokumen yang sudah diproses atau disetujui tidak dapat dihapus.');
        }

        try {
            // Hapus file fisik dari storage sebelum menghapus record database
            if ($dokumenProyekAkhir->file_path && Storage::disk('public')->exists($dokumenProyekAkhir->file_path)) {
                Storage::disk('public')->delete($dokumenProyekAkhir->file_path);
            }
            $dokumenProyekAkhir->delete();

            return redirect()->route('mahasiswa.dokumen.index')
                             ->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error("Error saat menghapus dokumen (ID: {$dokumenProyekAkhir->id}) oleh Mahasiswa ID: {$user->mahasiswa->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('mahasiswa.dokumen.index')
                             ->with('error', 'Gagal menghapus dokumen. Terjadi kesalahan.');
        }
    }
}
