<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Pastikan ini di-import jika menggunakan Hash::make() secara eksplisit
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class DosenController extends Controller
{
    public function index(Request $request) // <<< METHOD INDEX ADA DI SINI
    {
        $query = Dosen::query()->with(['user', 'prodi']);

        if ($request->filled('search')) {
            // ... logika pencarian ...
        }

        if ($request->filled('prodi_id')) {
            $query->where('prodi_id', $request->prodi_id);
        }

        $dosens = $query->orderBy(
            User::select('name')->whereColumn('users.id', 'dosen.user_id')
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
            // Validasi untuk User model
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username', // Sudah ada
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            // Validasi untuk Dosen model
            'nidn' => 'required|string|max:50|unique:dosen,nidn',
            'prodi_id' => 'required|exists:prodi,id',
            'spesialisasi' => 'nullable|string|max:255', // Tambahkan max length jika perlu
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat User terlebih dahulu
            $user = User::create([
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                // 'password' akan di-hash otomatis jika User model Anda sudah setup mutator/cast 'hashed'
                // Jika tidak, gunakan: 'password' => Hash::make($validatedData['password']),
                'password' => $validatedData['password'],
                'role' => 'dosen',
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Jika menggunakan Laravel 9+, error validasi biasanya tidak akan sampai ke catch ini
            // karena Laravel akan otomatis redirect back dengan error.
            // Namun, jika ada, ini cara menanganinya.
            DB::rollBack(); // Tidak perlu rollback jika hanya validasi gagal
            Log::warning("Validation error during dosen creation: ", $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating dosen: " . $e->getMessage() . "\n" . $e->getTraceAsString()); // Tambahkan trace untuk detail
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan dosen. Silakan cek log untuk detail atau hubungi administrator.');
        }
    }

    // ... (method show, edit, update, destroy tetap sama) ...
}
