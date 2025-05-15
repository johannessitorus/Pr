<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use Illuminate\Http\Request;

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
        $request->validate([
        'kode_prodi' => 'required|string|max:20|unique:db_pa1_prodi,kode_prodi',
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
        $request->validate([
        'kode_prodi' => 'required|string|max:20|unique:db_pa1_prodi,kode_prodi,' . $prodi->id,
        'nama_prodi' => 'required|string|max:100',
        'fakultas' => 'required|string|max:100',
    ]);

    $prodi->update($request->all());

    return redirect()->route('admin.prodi.index')
                     ->with('success', 'Prodi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prodi $prodi)
    {
        // Tambahkan validasi/pengecekan jika prodi masih terpakai oleh mahasiswa/dosen
    // sebelum menghapus untuk menjaga integritas data.
    // Contoh sederhana (Anda mungkin perlu logika lebih kompleks):
    if ($prodi->mahasiswas()->count() > 0 || $prodi->dosens()->count() > 0) {
        return redirect()->route('admin.prodi.index')
                         ->with('error', 'Prodi tidak dapat dihapus karena masih digunakan.');
    }

    $prodi->delete();

    return redirect()->route('admin.prodi.index')
                     ->with('success', 'Prodi berhasil dihapus.');
    }
}
