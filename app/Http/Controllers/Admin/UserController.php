<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Hanya jika tidak pakai 'hashed' cast atau Laravel < 9
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // Daftar peran yang valid yang bisa dikelola oleh controller ini
    // Anda bisa mengambil ini dari database jika peran dinamis, atau hardcode
    protected $validRoles = ['admin', 'dosen', 'mahasiswa']; // Sesuaikan jika ada peran lain

    public function __construct()
    {
        // Middleware bisa diletakkan di sini atau di route
        // $this->middleware('auth');
        // $this->middleware('admin'); // Pastikan middleware 'admin' sudah ada
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Jika parameter 'role' adalah 'dosen', redirect ke controller Dosen
        if ($request->input('role') === 'dosen') {
            // Asumsi route 'admin.dosen.index' sudah ada dan mengarah ke controller yang sesuai
            // Jika DosenController juga menampilkan user, mungkin Anda perlu membawa parameter search
            return redirect()->route('admin.dosen.index', $request->only('search'));
        }

        $query = User::query();

        // Filter berdasarkan peran (role)
        if ($request->filled('role') && in_array($request->role, $this->validRoles) && $request->role !== 'dosen') {
            $query->where('role', $request->role);
        } elseif (!$request->filled('role')) {
            // Jika tidak ada filter peran spesifik (dan bukan dosen), tampilkan semua selain dosen
            // atau tampilkan semua jika itu yang diinginkan. Di sini kita tampilkan semua yg bukan dosen.
            // Jika ingin tetap menampilkan dosen di sini, hapus kondisi whereNotIn
            $query->where('role', '!=', 'dosen');
        }
        // Jika $request->role adalah dosen, itu sudah di-redirect.

        // Filter berdasarkan pencarian nama, username, email
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('username', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }

        // Eager load relasi jika diperlukan (opsional, tergantung view)
        // $query->with(['mahasiswa', 'dosen']); // Perhatikan, 'dosen' mungkin tidak relevan jika sudah difilter

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        // Ambil semua peran unik dari tabel User untuk filter dropdown
        // Kecualikan 'dosen' jika halaman ini tidak menampilkannya
        $rolesForFilter = User::select('role')
                                ->where('role', '!=', 'dosen') // Sesuaikan jika ingin tetap ada 'dosen' di filter
                                ->distinct()
                                ->pluck('role');

        $pageTitle = 'Kelola Pengguna';
         if ($request->filled('role') && $request->role !== 'dosen') {
            $pageTitle .= ' (' . ucfirst($request->role) . ')';
        }


        return view('admin.users.index', compact('users', 'rolesForFilter', 'pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Peran yang bisa dipilih saat membuat user baru via UserController
        // Kecualikan 'dosen' jika pembuatan dosen sepenuhnya via DosenController
        $creatableRoles = array_filter($this->validRoles, function($role) {
            // return $role !== 'dosen'; // Uncomment jika Dosen dibuat HANYA via DosenController
            return true; // Biarkan semua role bisa dibuat user account-nya di sini
        });
        return view('admin.users.create', ['roles' => $creatableRoles]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in($this->validRoles)],
        ]);

        // Password akan di-hash otomatis oleh mutator 'hashed' di model User (jika Laravel 9+)
        // Jika tidak, gunakan: $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        // Catatan: Pembuatan entri detail di tabel 'dosen' atau 'mahasiswa'
        // diasumsikan akan dilakukan terpisah, misal melalui AdminDosenController atau AdminMahasiswaController
        // setelah user account ini dibuat.

        return redirect()->route('admin.users.index')->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    /**
     * Display the specified resource. (Opsional)
     */
    public function show(User $user)
    {
        // Jika peran user adalah dosen dan Anda punya halaman detail dosen yang lebih baik
        if ($user->isDosen() && $user->dosen) { // Pastikan relasi dosen ada
             return redirect()->route('admin.dosen.show', $user->dosen->id); // Asumsi route ada
        }
        // Jika tidak ada halaman show khusus, redirect ke edit atau tampilkan view sederhana
        return redirect()->route('admin.users.edit', $user);
        // atau: return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Peran yang bisa dipilih saat edit
        $editableRoles = $this->validRoles;
        return view('admin.users.edit', compact('user'), ['roles' => $editableRoles]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in($this->validRoles)],
        ]);

        $dataToUpdate = [
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
        ];

        if (!empty($validatedData['password'])) {
            // Password akan di-hash otomatis oleh mutator 'hashed' di model User (jika Laravel 9+)
            $dataToUpdate['password'] = $validatedData['password'];
        }

        $user->update($dataToUpdate);

        // Catatan: Jika role diubah ke/dari 'dosen' atau 'mahasiswa',
        // penanganan data di tabel detail (dosen/mahasiswa) perlu dipertimbangkan
        // (misal, menghapus record dosen jika role diubah jadi admin).
        // Ini bisa kompleks dan tergantung kebutuhan bisnis.

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        try {
            // SoftDeletes akan menangani ini, tidak menghapus permanen.
            // Jika user memiliki relasi (mahasiswa, dosen) yang memiliki onDelete('cascade')
            // pada foreign key di database, maka record terkait itu juga akan di soft-delete JIKA
            // model terkait (Mahasiswa, Dosen) juga menggunakan SoftDeletes.
            // Jika tidak, dan foreign key TIDAK nullable, maka akan error.
            // Solusi paling aman: Pastikan model Mahasiswa & Dosen juga SoftDeletes
            // atau tangani penghapusan record terkait secara manual sebelum menghapus user.

            // Contoh sederhana penanganan jika relasi harus dihapus juga (jika tidak cascade dan tidak softdelete di model anak):
            // if ($user->mahasiswa) { $user->mahasiswa->delete(); } // Ini akan menjalankan softdelete jika Mahasiswa model pakai SoftDeletes
            // if ($user->dosen) { $user->dosen->delete(); }

            $user->delete(); // Ini akan Soft Delete
            return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus (dipindahkan ke arsip).');

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Error soft deleting user {$user->id}: " . $e->getMessage());
            // Error ini lebih jarang terjadi dengan SoftDeletes, kecuali ada constraint yang sangat ketat.
            return redirect()->route('admin.users.index')->with('error', 'Gagal menghapus pengguna. Mungkin ada data terkait yang tidak bisa diubah.');
        }
    }
}
