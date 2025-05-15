<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use App\Models\Prodi; // Pastikan Prodi model diimport
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Untuk transaction
use Illuminate\Support\Facades\Hash; // Jika tidak pakai 'hashed' cast di User model
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;


class DosenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Dosen::query()->with(['user', 'prodi']); // Eager load user dan prodi

        // Filter berdasarkan pencarian (Nama User, Email User, NIDN Dosen)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('name', 'like', $searchTerm)
                              ->orWhere('email', 'like', $searchTerm)
                              ->orWhere('username', 'like', $searchTerm);
                })
                ->orWhere('nidn', 'like', $searchTerm);
            });
        }

        // Filter berdasarkan prodi_id jika ada
        if ($request->filled('prodi_id')) {
            $query->where('prodi_id', $request->prodi_id);
        }

        $dosens = $query->orderBy(
            User::select('name')->whereColumn('users.id', 'dosens.user_id') // Order by user's name
        )->paginate(10)->withQueryString();

        $prodis = Prodi::orderBy('nama_prodi')->get(); // Untuk filter dropdown

        return view('admin.dosen.index', compact('dosens', 'prodis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil daftar user yang belum jadi dosen dan memiliki role 'dosen' (jika ada), atau biarkan admin input baru
        // Atau, lebih umum, admin akan membuat akun user BARU saat membuat dosen
        $prodis = Prodi::orderBy('nama_prodi')->get();
        return view('admin.dosen.create', compact('prodis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // Validasi untuk User model
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            // Validasi untuk Dosen model
            'nidn' => 'required|string|max:50|unique:dosens,nidn',
            'prodi_id' => 'required|exists:prodi,id', // Pastikan tabel prodi ada dan kolom id
            'spesialisasi' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat User terlebih dahulu
            $user = User::create([
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                'password' => $validatedData['password'], // Akan di-hash oleh mutator di User model jika Laravel 9+
                // 'password' => Hash::make($validatedData['password']), // Jika Laravel < 9 atau tidak pakai mutator
                'role' => 'dosen', // Otomatis set role ke dosen
            ]);

            // 2. Buat Dosen dengan user_id yang baru dibuat
            Dosen::create([
                'user_id' => $user->id,
                'nidn' => $validatedData['nidn'],
                'prodi_id' => $validatedData['prodi_id'],
                'spesialisasi' => $validatedData['spesialisasi'],
            ]);

            DB::commit();
            return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating dosen: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan dosen. Terjadi kesalahan.');
        }
    }

    /**
     * Display the specified resource. (Opsional, bisa redirect ke edit)
     */
    public function show(Dosen $dosen)
    {
        $dosen->load(['user', 'prodi']); // Eager load
        // return view('admin.dosen.show', compact('dosen')); // Jika ada view show khusus
        return redirect()->route('admin.dosen.edit', $dosen);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dosen $dosen)
    {
        $dosen->load('user'); // Eager load user untuk mengisi form
        $prodis = Prodi::orderBy('nama_prodi')->get();
        return view('admin.dosen.edit', compact('dosen', 'prodis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dosen $dosen)
    {
        $validatedData = $request->validate([
            // Validasi untuk User model
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($dosen->user_id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($dosen->user_id)],
            'password' => 'nullable|string|min:8|confirmed', // Password opsional saat update
            // Validasi untuk Dosen model
            'nidn' => ['required', 'string', 'max:50', Rule::unique('dosens')->ignore($dosen->id)],
            'prodi_id' => 'required|exists:prodi,id',
            'spesialisasi' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // 1. Update User terkait
            $userData = [
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
            ];
            if (!empty($validatedData['password'])) {
                // $userData['password'] = Hash::make($validatedData['password']); // Jika Laravel < 9
                $userData['password'] = $validatedData['password']; // Akan di-hash oleh mutator
            }
            $dosen->user->update($userData);

            // 2. Update Dosen
            $dosen->update([
                'nidn' => $validatedData['nidn'],
                'prodi_id' => $validatedData['prodi_id'],
                'spesialisasi' => $validatedData['spesialisasi'],
            ]);

            DB::commit();
            return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating dosen {$dosen->id}: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data dosen. Terjadi kesalahan.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dosen $dosen)
    {
        DB::beginTransaction();
        try {
            // Jika menggunakan SoftDeletes di User dan Dosen model (direkomendasikan)
            // Menghapus dosen juga harus "menghapus" user terkaitnya
            // atau setidaknya menonaktifkan user jika tidak ingin dihapus total.

            // Opsi 1: Soft delete User dan Dosen (jika keduanya pakai SoftDeletes)
            if ($dosen->user) {
                $dosen->user->delete(); // Akan soft delete User jika User model pakai SoftDeletes
            }
            $dosen->delete(); // Akan soft delete Dosen jika Dosen model pakai SoftDeletes

            // Opsi 2: Jika hanya Dosen yang soft delete, User mungkin tetap aktif (tergantung kebutuhan)
            // $dosen->delete();

            DB::commit();
            return redirect()->route('admin.dosen.index')->with('success', 'Data dosen berhasil dihapus (diarsipkan).');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting dosen {$dosen->id}: " . $e->getMessage());
            // Tangani error karena foreign key jika tidak pakai soft delete atau cascade yang benar
            return redirect()->route('admin.dosen.index')->with('error', 'Gagal menghapus dosen. Mungkin ada data terkait.');
        }
    }
}
