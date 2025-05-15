<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $query = Dosen::query()->with(['user', 'prodi']); // Eager load relasi user dan prodi

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('nidn', 'like', "%{$search}%");
        }

        if ($request->filled('prodi_id')) {
            $query->where('prodi_id', $request->prodi_id);
        }

        // Pengurutan berdasarkan nama user
        $dosens = $query->orderBy(
            User::select('name') // Subquery untuk mendapatkan nama user
                ->whereColumn('users.id', 'dosen.user_id') // Kondisi join
                ->orderBy('name') // Urutkan berdasarkan nama
                ->limit(1) // Ambil hanya satu nama (seharusnya memang hanya satu)
        )->paginate(10)->withQueryString();


        $prodis = Prodi::orderBy('nama_prodi')->get();

        return view('admin.dosen.index', compact('dosens', 'prodis'));
    }

    public function create()
    {
        $prodis = Prodi::orderBy('nama_prodi')->get();
        return view('admin.dosen.create', compact('prodis'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'nidn' => 'required|string|max:50|unique:dosen,nidn',
            'prodi_id' => 'required|exists:prodi,id',
            'spesialisasi' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                'password' => $validatedData['password'], // Akan di-hash oleh mutator/cast di model User
                'role' => 'dosen',
            ]);

            Dosen::create([
                'user_id' => $user->id,
                'nidn' => $validatedData['nidn'],
                'prodi_id' => $validatedData['prodi_id'],
                'spesialisasi' => $validatedData['spesialisasi'],
            ]);

            DB::commit();
            return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil ditambahkan.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack(); // Meskipun jarang sampai sini jika validasi otomatis Laravel aktif
            Log::warning("Validation error during dosen creation: ", $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating dosen: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan dosen. Silakan cek log atau hubungi administrator.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Dosen $dosen)
    {
        // Eager load relasi untuk ditampilkan di view
        $dosen->load(['user', 'prodi']);
        return view('admin.dosen.show', compact('dosen'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dosen $dosen)
    {
        // Eager load relasi untuk form
        $dosen->load(['user']);
        $prodis = Prodi::orderBy('nama_prodi')->get();
        return view('admin.dosen.edit', compact('dosen', 'prodis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dosen $dosen)
    {
        $user = $dosen->user; // Dapatkan instance User terkait

        $validatedData = $request->validate([
            // Validasi untuk User model
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed', // Password opsional saat update
            // Validasi untuk Dosen model
            'nidn' => [
                'required',
                'string',
                'max:50',
                Rule::unique('dosen', 'nidn')->ignore($dosen->id),
            ],
            'prodi_id' => 'required|exists:prodi,id',
            'spesialisasi' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // 1. Update User
            $userDataToUpdate = [
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
            ];
            // Hanya update password jika diisi
            if (!empty($validatedData['password'])) {
                $userDataToUpdate['password'] = $validatedData['password']; // Akan di-hash oleh mutator/cast
            }
            $user->update($userDataToUpdate);

            // 2. Update Dosen
            $dosen->update([
                'nidn' => $validatedData['nidn'],
                'prodi_id' => $validatedData['prodi_id'],
                'spesialisasi' => $validatedData['spesialisasi'],
            ]);

            DB::commit();
            return redirect()->route('admin.dosen.index')->with('success', 'Data Dosen berhasil diperbarui.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::warning("Validation error during dosen update (ID: {$dosen->id}): ", $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating dosen (ID: {$dosen->id}): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data dosen. Silakan cek log atau hubungi administrator.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dosen $dosen)
    {
        // PENTING: Tambahkan pengecekan relasi di sini sebelum menghapus
        // Misalnya, jika dosen masih memiliki jadwal mengajar, bimbingan, dll.
        // Contoh (asumsi Anda punya relasi di model Dosen):
        // if ($dosen->jadwals()->exists() || $dosen->bimbingans()->exists()) {
        //     return redirect()->route('admin.dosen.index')->with('error', 'Dosen tidak dapat dihapus karena masih memiliki data terkait (misal: jadwal, bimbingan).');
        // }

        DB::beginTransaction();
        try {
            // Hapus Dosen terlebih dahulu atau User? Tergantung constraint FK
            // Jika user_id di tabel dosen memiliki ON DELETE CASCADE, maka menghapus User akan menghapus Dosen.
            // Jika tidak, atau jika ingin lebih eksplisit:

            $user = $dosen->user; // Dapatkan user terkait

            $dosen->delete(); // Hapus record Dosen

            if ($user) {
                // Periksa apakah user ini hanya terkait dengan Dosen ini
                // Jika User bisa memiliki role lain atau tidak selalu terikat dengan Dosen,
                // Anda mungkin tidak ingin menghapus User-nya.
                // Untuk kasus ini, kita asumsikan User 'dosen' selalu terkait dengan satu record Dosen.
                $user->delete(); // Hapus record User
            }

            DB::commit();
            return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting dosen (ID: {$dosen->id}): " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('admin.dosen.index')->with('error', 'Gagal menghapus dosen. Silakan cek log atau hubungi administrator.');
        }
    }
}
