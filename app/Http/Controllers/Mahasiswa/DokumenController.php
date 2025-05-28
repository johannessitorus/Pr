<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\DokumenProyekAkhir;
use App\Models\JenisDokumen;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DokumenController extends Controller
{
    /**
     * Display a listing of the submitted documents for the authenticated mahasiswa.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa) {
            return redirect()->route('dashboard')
                             ->with('error', 'Data mahasiswa tidak ditemukan. Silakan hubungi admin.');
        }

        $mahasiswa = $user->mahasiswa;
        $dokumens = DokumenProyekAkhir::where('mahasiswa_id', $mahasiswa->id)
                        ->with('jenisDokumen')
                        ->latest()
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
            return redirect()->route('mahasiswa.dokumen.index')
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
            'file_dokumen' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,zip,rar|max:10240', // Max 10MB
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
            $ukuranFile = $file->getSize();

            $jenisDokumen = JenisDokumen::find($request->jenis_dokumen_id);
            $jenisDokumenSlug = Str::slug($jenisDokumen->nama_jenis ?? 'dokumen');
            $namaFileUnik = Str::slug($mahasiswa->nim . '_' . $jenisDokumenSlug . '_' . time() . '_' . Str::random(5)) . '.' . $ekstensiFile;

            $pathPenyimpanan = 'dokumen_mahasiswa/' . Str::slug($mahasiswa->nim); // Gunakan slug untuk nim jika mengandung karakter khusus
            $filePath = $file->storeAs($pathPenyimpanan, $namaFileUnik, 'public');

            if ($filePath) {
                $versiTerakhir = DokumenProyekAkhir::where('mahasiswa_id', $mahasiswa->id)
                                    ->where('jenis_dokumen_id', $request->jenis_dokumen_id)
                                    ->max('versi');
                $versiBaru = $versiTerakhir ? $versiTerakhir + 1 : 1;

                DokumenProyekAkhir::create([
                    'mahasiswa_id' => $mahasiswa->id,
                    'jenis_dokumen_id' => $request->jenis_dokumen_id,
                    'nama_file_asli' => $namaFileAsli,
                    'nama_file_unik' => $namaFileUnik,
                    'file_path' => $filePath,
                    'ekstensi_file' => $ekstensiFile,
                    'ukuran_file' => $ukuranFile,
                    'versi' => $versiBaru,
                    'catatan_mahasiswa' => $request->catatan_mahasiswa,
                    'status_review' => 'pending',
                    'uploaded_at' => now(), // Eksplisit set uploaded_at
                    // 'reviewed_by', 'reviewed_at' akan null by default
                ]);

                return redirect()->route('mahasiswa.dokumen.index')
                                 ->with('success', 'Dokumen berhasil disubmit.');
            } else {
                Log::error("Gagal menyimpan file untuk mahasiswa ID: {$mahasiswa->id}. Path: {$pathPenyimpanan}, Nama Unik: {$namaFileUnik}");
                return back()->with('error', 'Gagal menyimpan file. Silakan coba lagi atau hubungi admin.')->withInput();
            }
        }
        return back()->with('error', 'File dokumen tidak ditemukan atau tidak valid.')->withInput();
    }

    /**
     * Display the specified resource.
     */
    public function show(DokumenProyekAkhir $dokumenProyekAkhir)
    {
        $user = Auth::user();
        $isMahasiswa = $user && $user->mahasiswa && $dokumenProyekAkhir->mahasiswa_id == $user->mahasiswa->id;
        $isDosen = $user && $user->dosen && $dokumenProyekAkhir->mahasiswa && $dokumenProyekAkhir->mahasiswa->dosen_pembimbing_id == $user->dosen->id;
        Log::info('Akses dokumen', [
            'user_id' => $user->id ?? null,
            'user_mahasiswa_id' => $user->mahasiswa->id ?? null,
            'dokumen_mahasiswa_id' => $dokumenProyekAkhir->mahasiswa_id,
            'isMahasiswa' => $isMahasiswa,
            'isDosen' => $isDosen,
            'dokumen_id' => $dokumenProyekAkhir->id,
        ]);
        if (!$isMahasiswa && !$isDosen) {
            return redirect()->route('mahasiswa.dokumen.index')
                             ->with('error', 'Anda tidak memiliki izin untuk mengakses dokumen ini.');
        }

        $dokumenProyekAkhir->load(['jenisDokumen', 'reviewer.user', 'mahasiswa.user']);
        return view('mahasiswa.dokumen.show', compact('dokumenProyekAkhir'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DokumenProyekAkhir $dokumenProyekAkhir)
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa || $dokumenProyekAkhir->mahasiswa_id !== $user->mahasiswa->id) {
            abort(403, 'ANDA TIDAK DIIZINKAN MENGEDIT DOKUMEN INI.');
        }

        // Mahasiswa boleh mengedit jika statusnya 'pending' atau 'revision_needed'
        if (!in_array($dokumenProyekAkhir->status_review, ['pending', 'revision_needed'])) {
             return redirect()->route('mahasiswa.dokumen.show', $dokumenProyekAkhir->id)
                              ->with('error', 'Dokumen dengan status ini tidak dapat diedit.');
        }

        // Jenis dokumen biasanya tidak diubah saat edit, tapi bisa disiapkan jika diperlukan
        // $jenisDokumens = JenisDokumen::orderBy('nama_jenis')->get();

        // Data yang diperlukan view: dokumen itu sendiri
        return view('mahasiswa.dokumen.edit', compact('dokumenProyekAkhir'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DokumenProyekAkhir $dokumenProyekAkhir)
    {
        $user = Auth::user();
        if (!$user || !$user->mahasiswa || $dokumenProyekAkhir->mahasiswa_id !== $user->mahasiswa->id) {
            abort(403, 'ANDA TIDAK DIIZINKAN MENGUPDATE DOKUMEN INI.');
        }

        // Pastikan hanya dokumen dengan status tertentu yang bisa diupdate
        if (!in_array($dokumenProyekAkhir->status_review, ['pending', 'revision_needed'])) {
            return redirect()->route('mahasiswa.dokumen.show', $dokumenProyekAkhir->id)
                             ->with('error', 'Dokumen dengan status ini tidak dapat diupdate.');
        }

        $request->validate([
            // Jenis dokumen biasanya tidak diubah saat edit. Jika boleh, uncomment dan tambahkan di form.
            // 'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
            'file_dokumen' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,rar|max:10240', // Nullable, karena mungkin hanya edit catatan
            'catatan_mahasiswa' => 'nullable|string|max:2000',
        ]);

        $mahasiswa = $user->mahasiswa;
        $updateData = [
            'catatan' => $request->catatan_mahasiswa,
            'uploaded_at' => now(), // Perbarui waktu unggah karena ada perubahan
        ];

        // Jika ini adalah revisi (dari 'revision_needed'), status kembali ke 'pending'
        // Jika edit saat masih 'pending', status tetap 'pending'
        // Jadi, selalu set ke 'pending' setelah update oleh mahasiswa
        $updateData['status_review'] = 'pending';
        $updateData['reviewed_by'] = null;   // Reset reviewer
        $updateData['reviewed_at'] = null;   // Reset waktu review
        // $updateData['catatan_dosen'] = null; // Pertimbangkan apakah catatan dosen sebelumnya perlu dihapus

        if ($request->hasFile('file_dokumen')) {
            $file = $request->file('file_dokumen');
            $ekstensiFile = $file->getClientOriginalExtension();

            // Dapatkan slug jenis dokumen dari dokumen yang ada
            $jenisDokumenSlug = Str::slug($dokumenProyekAkhir->jenisDokumen->nama_jenis ?? 'dokumen');
            $namaFileUnikBaru = Str::slug($mahasiswa->nim . '_' . $jenisDokumenSlug . '_' . time() . '_' . Str::random(5)) . '.' . $ekstensiFile;

            $pathPenyimpanan = 'dokumen_mahasiswa/' . Str::slug($mahasiswa->nim);
            $filePathBaru = $file->storeAs($pathPenyimpanan, $namaFileUnikBaru, 'public');

            if ($filePathBaru) {
                // Hapus file lama jika ada dan berhasil simpan file baru
                if ($dokumenProyekAkhir->file_path && Storage::disk('public')->exists($dokumenProyekAkhir->file_path)) {
                    Storage::disk('public')->delete($dokumenProyekAkhir->file_path);
                }
                $updateData['nama_file_asli'] = $namaFileUnikBaru;
                $updateData['file_path'] = $filePathBaru;
                // Versi baru: increment dari versi terakhir untuk jenis dokumen ini
                // atau bisa juga dari versi dokumen yang sedang diedit + 1
                $updateData['versi'] = $dokumenProyekAkhir->versi + 1;
            } else {
                Log::error("Gagal menyimpan file (update) untuk mahasiswa ID: {$mahasiswa->id}. Path: {$pathPenyimpanan}, Nama Unik: {$namaFileUnikBaru}");
                return back()->with('error', 'Gagal menyimpan file revisi. Silakan coba lagi atau hubungi admin.')->withInput();
            }
        }
        // Jika tidak ada file baru diupload, versi tidak perlu diubah kecuali ada aturan bisnis lain

        $dokumenProyekAkhir->update($updateData);

        return redirect()->route('mahasiswa.dokumen.show', $dokumenProyekAkhir->id)
                         ->with('success', 'Dokumen berhasil diupdate.');
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

        // Mahasiswa hanya boleh menghapus dokumen jika statusnya 'pending' atau 'revision_needed'.
        if (!in_array($dokumenProyekAkhir->status_review, ['pending', 'revision_needed'])) {
            return redirect()->route('mahasiswa.dokumen.index')
                             ->with('error', 'Dokumen yang sudah diproses atau disetujui tidak dapat dihapus.');
        }

        try {
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
