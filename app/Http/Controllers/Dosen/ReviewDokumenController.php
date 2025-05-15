<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\DokumenProyekAkhir;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ReviewDokumenController extends Controller
{
    /**
     * Display a listing of documents pending review for the authenticated dosen.
     */
    public function index()
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            return redirect()->route('dashboards.dosen')->with('error', 'Data dosen tidak ditemukan.');
        }

        $mahasiswaBimbinganIds = Mahasiswa::where('dosen_pembimbing_id', $dosen->id)->pluck('id');

        $dokumensPending = DokumenProyekAkhir::whereIn('mahasiswa_id', $mahasiswaBimbinganIds)
            // Anda bisa filter berdasarkan status tertentu, misal hanya 'pending'
            // atau semua yang belum 'approved'
            ->whereIn('status_review', ['pending', 'revision_needed'])
            ->with(['mahasiswa.user', 'jenisDokumen'])
            ->latest('updated_at')
            ->paginate(10);

        return view('dosen.review_dokumen.index', compact('dokumensPending'));
    }

    /**
     * Show the form for processing the review of a specific document.
     */
    public function prosesReview(DokumenProyekAkhir $dokumenProyekAkhir)
    {
        // Otorisasi: Pastikan dosen ini adalah pembimbing dari mahasiswa pemilik dokumen
        $dosen = Auth::user()->dosen;
        if (!$dosen || !$dokumenProyekAkhir->mahasiswa || $dokumenProyekAkhir->mahasiswa->dosen_pembimbing_id !== $dosen->id) {
            abort(403, 'Anda tidak berhak mereview dokumen ini.');
        }

        $dokumenProyekAkhir->load(['mahasiswa.user', 'jenisDokumen']);
        return view('dosen.review_dokumen.proses', compact('dokumenProyekAkhir'));
    }

    /**
     * Update the review status and notes for the specified document.
     */
    public function updateReview(Request $request, DokumenProyekAkhir $dokumenProyekAkhir)
    {
        // Otorisasi
        $dosen = Auth::user()->dosen;
        if (!$dosen || !$dokumenProyekAkhir->mahasiswa || $dokumenProyekAkhir->mahasiswa->dosen_pembimbing_id !== $dosen->id) {
            return redirect()->route('dosen.review-dokumen.index')->with('error', 'Aksi tidak diizinkan.');
        }

        $request->validate([
            'status_review' => ['required', Rule::in(['approved', 'revision_needed', 'rejected'])],
            'catatan_reviewer' => 'nullable|string|max:2000',
        ]);

        try {
            $dokumenProyekAkhir->status_review = $request->status_review;
            $dokumenProyekAkhir->catatan_reviewer = $request->catatan_reviewer;
            $dokumenProyekAkhir->reviewed_by = Auth::id(); // ID user dosen yang mereview
            $dokumenProyekAkhir->reviewed_at = now();
            $dokumenProyekAkhir->save();

            Log::info("Dosen (ID: ".Auth::id().") mereview dokumen (ID: {$dokumenProyekAkhir->id}) dengan status: {$request->status_review}");

            // TODO: Kirim notifikasi ke mahasiswa jika perlu

            return redirect()->route('dosen.review-dokumen.index')->with('success', 'Review dokumen berhasil disimpan.');

        } catch (\Exception $e) {
            Log::error("Error saat menyimpan review dokumen (ID: {$dokumenProyekAkhir->id}): " . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan review dokumen. Silakan coba lagi.')->withInput();
        }
    }
}
