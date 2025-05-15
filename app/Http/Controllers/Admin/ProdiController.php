<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // <-- TAMBAHKAN INI


class ProdiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prodis = Prodi::orderBy('nama_prodi')->paginate(10); // Ambil semua prodi, urutkan, paginasi
    return view('admin.prodi.index', compact('prodis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.prodi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
        'kode_prodi' => 'required|string|max:20|unique:prodi,kode_prodi,',
        'nama_prodi' => 'required|string|max:100',
        'fakultas' => 'required|string|max:100',
    ]);

    Prodi::create($request->all());

    return redirect()->route('admin.prodi.index')
                     ->with('success', 'Prodi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Prodi $prodi)
    {
        return view('admin.prodi.show', compact('prodi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prodi $prodi)
    {
        return view('admin.prodi.edit', compact('prodi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prodi $prodi)
    {
        // Validasi data
        $validatedData = $request->validate([
            // SEBELUM (MUNGKIN INI PENYEBABNYA JIKA ANDA MENULIS NAMA TABEL SECARA MANUAL DAN SALAH)
            // 'kode_prodi' => 'required|string|max:10|unique:db_pa1_prodi,kode_prodi,' . $prodi->id,

            // SESUDAH (PILIHAN 1: Menggunakan nama tabel dari model Anda)
            'kode_prodi' => 'required|string|max:20|unique:prodi,kode_prodi,' . $prodi->id, // Gunakan 'prodi' sesuai $table di model
            // ATAU (PILIHAN 2: Cara yang lebih disarankan, menggunakan class Model)
            // 'kode_prodi' => [
            //     'required',
            //     'string',
            //     'max:20', // Sesuaikan max length dengan migrasi
            //     Rule::unique(Prodi::class)->ignore($prodi->id),
            // ],
            'nama_prodi' => 'required|string|max:100', // Sesuaikan max length dengan migrasi
            'fakultas'   => 'nullable|string|max:100', // Sesuaikan max length dengan migrasi
        ]);

        try {
            $prodi->update($validatedData);
            return redirect()->route('admin.prodi.index')->with('success', 'Prodi berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui prodi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prodi $prodi)
    {
        // Tambahkan validasi/pengecekan jika prodi masih terpakai oleh mahasiswa/dosen
    // sebelum menghapus untuk menjaga integritas data.
    // Contoh sederhana (Anda mungkin perlu logika lebih kompleks):
    if ($prodi->mahasiswa()->count() > 0 || $prodi->dosen()->count() > 0) {
        return redirect()->route('admin.prodi.index')
                         ->with('error', 'Prodi tidak dapat dihapus karena masih digunakan.');
    }

    $prodi->delete();

    return redirect()->route('admin.prodi.index')
                     ->with('success', 'Prodi berhasil dihapus.');
    }
}
