<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisDokumen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;      // Untuk validasi unique saat update
use Illuminate\Support\Facades\Log;  // Untuk logging jika terjadi error

class JenisDokumenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // PENYESUAIAN: Karena $timestamps = false di model,
        // kita tidak bisa menggunakan latest() tanpa argumen atau orderBy('created_at').
        // Urutkan berdasarkan kolom lain yang ada, misalnya 'nama_jenis' (A-Z) atau 'id' (DESC untuk data terbaru berdasarkan ID).
        $jenisDokumens = JenisDokumen::orderBy('nama_jenis', 'asc')->paginate(10);
        // Alternatif: urutkan berdasarkan ID (jika ID auto-increment dan mencerminkan urutan penambahan)
        // $jenisDokumens = JenisDokumen::orderBy('id', 'desc')->paginate(10);

        return view('admin.jenis_dokumen.index', compact('jenisDokumens'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.jenis_dokumen.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_jenis' => 'required|string|max:100|unique:jenis_dokumen,nama_jenis',
            'deskripsi' => 'nullable|string|max:2000',
        ]);

        try {
            JenisDokumen::create($validatedData);
            Log::info("Jenis dokumen baru ditambahkan: {$validatedData['nama_jenis']} oleh Admin ID: " . auth()->id());

            return redirect()->route('admin.jenis-dokumen.index')
                             ->with('success', 'Jenis dokumen berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error("Error saat menambahkan jenis dokumen: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()
                         ->with('error', 'Gagal menambahkan jenis dokumen. Silakan coba lagi. Detail: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(JenisDokumen $jenisDokuman)
    {
        // Jika rute show di-exclude, method ini tidak akan pernah terpanggil.
        // Jika dipanggil, redirect ke index adalah pilihan yang baik untuk entitas ini.
        return redirect()->route('admin.jenis-dokumen.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JenisDokumen $jenisDokuman)
    {
        return view('admin.jenis_dokumen.edit', compact('jenisDokuman'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JenisDokumen $jenisDokuman)
    {
        $validatedData = $request->validate([
            'nama_jenis' => [
                'required',
                'string',
                'max:100',
                Rule::unique('jenis_dokumen', 'nama_jenis')->ignore($jenisDokuman->id),
            ],
            'deskripsi' => 'nullable|string|max:2000',
        ]);

        try {
            $jenisDokuman->update($validatedData);
            Log::info("Jenis dokumen (ID: {$jenisDokuman->id}) diperbarui: {$validatedData['nama_jenis']} oleh Admin ID: " . auth()->id());

            return redirect()->route('admin.jenis-dokumen.index')
                             ->with('success', 'Jenis dokumen berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("Error saat memperbarui jenis dokumen (ID: {$jenisDokuman->id}): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()
                         ->with('error', 'Gagal memperbarui jenis dokumen. Silakan coba lagi. Detail: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JenisDokumen $jenisDokuman)
    {
        try {
            // Opsional: Periksa apakah jenis dokumen ini sedang digunakan sebelum menghapus
            // if ($jenisDokuman->dokumenProyekAkhir()->exists()) { // Asumsi ada relasi dokumenProyekAkhir()
            //     return redirect()->route('admin.jenis-dokumen.index')
            //                      ->with('error', 'Gagal menghapus. Jenis dokumen ini masih digunakan oleh beberapa dokumen.');
            // }

            $namaJenisLog = $jenisDokuman->nama_jenis;
            $idLog = $jenisDokuman->id;
            $jenisDokuman->delete();
            Log::info("Jenis dokumen (ID: {$idLog}) '{$namaJenisLog}' dihapus oleh Admin ID: " . auth()->id());

            return redirect()->route('admin.jenis-dokumen.index')
                             ->with('success', "Jenis dokumen '{$namaJenisLog}' berhasil dihapus.");
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("QueryException saat menghapus jenis dokumen (ID: {$jenisDokuman->id}): ". $e->getMessage(), ['errorInfo' => $e->errorInfo]);
            if (str_contains(strtolower($e->getMessage()), 'foreign key constraint') || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)) {
                return redirect()->route('admin.jenis-dokumen.index')
                                 ->with('error', 'Gagal menghapus. Jenis dokumen ini mungkin masih terikat dengan data dokumen lain.');
            }
            return redirect()->route('admin.jenis-dokumen.index')
                             ->with('error', 'Gagal menghapus jenis dokumen karena masalah database.');
        } catch (\Exception $e) {
            Log::error("Error umum saat menghapus jenis dokumen (ID: {$jenisDokuman->id}): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.jenis-dokumen.index')
                         ->with('error', 'Gagal menghapus jenis dokumen. Silakan coba lagi. Detail: ' . $e->getMessage());
        }
    }
}
