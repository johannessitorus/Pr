<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Untuk logging error
use Illuminate\Validation\Rule;     // Untuk validasi unique saat update

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mahasiswas = Mahasiswa::with(['user', 'prodi'])
                        ->latest('created_at') // Urutkan berdasarkan mahasiswa terbaru
                        ->paginate(10);
        // Untuk debug data tidak muncul, Anda bisa uncomment ini:
        // if ($mahasiswas->isNotEmpty()) {
        //     Log::info('Data Mahasiswa yang dikirim ke view index:', $mahasiswas->toArray());
        // }
        return view('admin.mahasiswa.index', compact('mahasiswas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $prodis = Prodi::orderBy('nama_prodi')->get();
        return view('admin.mahasiswa.create', compact('prodis'));
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
            'nim' => 'required|string|max:20|unique:mahasiswa,nim',
            'prodi_id' => 'required|exists:prodi,id',
            'angkatan' => 'required|digits:4|integer|min:1900|max:'.(date('Y')+5),
            'nomor_kelompok' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat User baru
            $userData = [
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => 'mahasiswa',
            ];
            Log::info('[AdminMahasiswaController@store] Data User yang akan dibuat:', $userData);
            $user = User::create($userData);

            // 2. Buat data Mahasiswa terkait
            $mahasiswaData = [
                'nim' => $validatedData['nim'],
                'prodi_id' => $validatedData['prodi_id'],
                'angkatan' => $validatedData['angkatan'],
                'nomor_kelompok' => $validatedData['nomor_kelompok'] ?? null,
                'status_proyek_akhir' => 'belum_ada', // Status default
            ];
            Log::info('[AdminMahasiswaController@store] Data Mahasiswa yang akan dibuat (sebelum relasi):', $mahasiswaData);

            $mahasiswa = new Mahasiswa($mahasiswaData);
            $user->mahasiswa()->save($mahasiswa); // Ini akan mengisi mahasiswa.user_id dengan $user->id

            // Verifikasi data setelah tersimpan (untuk debugging "data tidak muncul")
            $savedMahasiswa = Mahasiswa::with('user', 'prodi')->find($mahasiswa->id);
            Log::info('[AdminMahasiswaController@store] Data Mahasiswa setelah disimpan dan direlasi:', $savedMahasiswa ? $savedMahasiswa->toArray() : ['error' => 'Mahasiswa tidak ditemukan setelah save']);

            DB::commit();

            activity()
               ->causedBy(auth()->user())
               ->performedOn($user) // Subjek utama bisa user atau mahasiswa
               ->withProperties(['mahasiswa_id' => $mahasiswa->id, 'user_id' => $user->id, 'nim' => $mahasiswa->nim])
               ->log("Admin menambahkan mahasiswa baru: {$user->name}");

            return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa baru berhasil ditambahkan.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('[AdminMahasiswaController@store] Kesalahan validasi: ', $e->errors());
            return back()->withErrors($e->errors())->withInput()->with('error', 'Gagal menambahkan mahasiswa. Periksa kembali data yang Anda masukkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[AdminMahasiswaController@store] Error saat menambahkan mahasiswa baru: '. $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Gagal menambahkan mahasiswa. Silakan coba lagi. Detail: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['user', 'prodi', 'dosenPembimbing.user']);
        return view('admin.mahasiswa.show', compact('mahasiswa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load('user');
        $prodis = Prodi::orderBy('nama_prodi')->get();
        return view('admin.mahasiswa.edit', compact('mahasiswa', 'prodis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $user = $mahasiswa->user;
        if (!$user) {
            Log::error("[AdminMahasiswaController@update] User tidak ditemukan untuk Mahasiswa ID: {$mahasiswa->id}");
            return back()->withInput()->with('error', 'Gagal memperbarui data: User terkait tidak ditemukan.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'nim' => ['required', 'string', 'max:20', Rule::unique('mahasiswa')->ignore($mahasiswa->id)],
            'prodi_id' => 'required|exists:prodi,id',
            'angkatan' => 'required|digits:4|integer|min:1900|max:'.(date('Y')+5),
            'nomor_kelompok' => 'nullable|string|max:50',
            'status_proyek_akhir' => ['required', Rule::in(['belum_ada', 'pengajuan_judul', 'bimbingan', 'selesai', 'revisi'])],
        ]);

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
            ];
            if (!empty($validatedData['password'])) {
                $userData['password'] = Hash::make($validatedData['password']);
            }
            $user->update($userData);

            $mahasiswa->update([
                'nim' => $validatedData['nim'],
                'prodi_id' => $validatedData['prodi_id'],
                'angkatan' => $validatedData['angkatan'],
                'nomor_kelompok' => $validatedData['nomor_kelompok'] ?? null,
                'status_proyek_akhir' => $validatedData['status_proyek_akhir'],
            ]);

            DB::commit();

            activity()
               ->causedBy(auth()->user())
               ->performedOn($user)
               ->withProperties(['mahasiswa_id' => $mahasiswa->id, 'user_id' => $user->id, 'nim' => $mahasiswa->nim])
               ->log("Admin memperbarui data mahasiswa: {$user->name}");

            return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil diperbarui.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('[AdminMahasiswaController@update] Kesalahan validasi: ', $e->errors());
            return back()->withErrors($e->errors())->withInput()->with('error', 'Gagal memperbarui data. Periksa kembali data yang Anda masukkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[AdminMahasiswaController@update] Error saat memperbarui mahasiswa (ID: {$mahasiswa->id}): ". $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Gagal memperbarui data mahasiswa. Silakan coba lagi. Detail: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        DB::beginTransaction();
        try {
            $user = $mahasiswa->user; // Ambil user terkait SEBELUM mahasiswa dihapus

            Log::info("[AdminMahasiswaController@destroy] Mencoba menghapus Mahasiswa ID: {$mahasiswa->id}, NIM: {$mahasiswa->nim}");

            // PENTING: Tangani atau hapus data yang BERELASI DENGAN MAHASISWA INI
            // sebelum menghapus record mahasiswa itu sendiri, jika foreign key
            // pada tabel lain yang merujuk ke 'mahasiswa_id' tidak diset 'ON DELETE CASCADE'.
            // Ini adalah penyebab umum error "tidak bisa dihapus".
            // Contoh (Anda HARUS menyesuaikan ini dengan relasi dan model Anda):
            //
            // if (method_exists($mahasiswa, 'requestJuduls') && $mahasiswa->requestJuduls()->exists()) {
            //     Log::info("[AdminMahasiswaController@destroy] Menghapus request judul terkait Mahasiswa ID: {$mahasiswa->id}");
            //     $mahasiswa->requestJuduls()->delete(); // Pastikan relasi requestJuduls() ada di model Mahasiswa
            // }
            // if (method_exists($mahasiswa, 'historyBimbingans') && $mahasiswa->historyBimbingans()->exists()) {
            //     Log::info("[AdminMahasiswaController@destroy] Menghapus history bimbingan terkait Mahasiswa ID: {$mahasiswa->id}");
            //     $mahasiswa->historyBimbingans()->delete(); // Pastikan relasi historyBimbingans() ada
            // }
            // if (method_exists($mahasiswa, 'dokumenProyekAkhirs') && $mahasiswa->dokumenProyekAkhirs()->exists()) {
            //     Log::info("[AdminMahasiswaController@destroy] Menghapus dokumen proyek akhir terkait Mahasiswa ID: {$mahasiswa->id}");
            //     // Untuk dokumen dengan file, Anda mungkin juga perlu menghapus file fisiknya
            //     // foreach($mahasiswa->dokumenProyekAkhirs as $doc) { Storage::delete($doc->file_path); }
            //     $mahasiswa->dokumenProyekAkhirs()->delete();
            // }
            // Tambahkan untuk relasi lain seperti pesan forum jika mahasiswa_id ada di sana, dll.

            $mahasiswaNim = $mahasiswa->nim; // Simpan nim untuk log sebelum objek dihapus
            $mahasiswa->delete(); // Hapus record mahasiswa
            Log::info("[AdminMahasiswaController@destroy] Mahasiswa NIM: {$mahasiswaNim} berhasil dihapus dari tabel mahasiswa.");

            $userNameForLog = 'User tidak ditemukan atau sudah terhapus sebelumnya';
            $userIdForLog = null;
            if ($user) {
                $userNameForLog = $user->name; // Simpan nama untuk log
                $userIdForLog = $user->id;
                $user->delete(); // Hapus user terkait
                Log::info("[AdminMahasiswaController@destroy] User '{$userNameForLog}' (ID: {$userIdForLog}) berhasil dihapus dari tabel users.");
            } else {
                Log::warning("[AdminMahasiswaController@destroy] Tidak ada user terkait yang ditemukan untuk Mahasiswa NIM: {$mahasiswaNim} saat mencoba menghapus user.");
            }

            DB::commit();

            activity()
               ->causedBy(auth()->user())
               // Jika user sudah dihapus, $user mungkin null. Subjek log bisa saja ID atau deskripsi.
               ->withProperties(['deleted_mahasiswa_nim' => $mahasiswaNim, 'deleted_user_id' => $userIdForLog])
               ->log("Admin menghapus mahasiswa: {$userNameForLog} (NIM: {$mahasiswaNim})");

            return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus beserta akun pengguna terkait (jika ada).');

        } catch (\Illuminate\Database\QueryException $e) { // Lebih spesifik untuk error DB
            DB::rollBack();
            $errorMessage = $e->getMessage();
            Log::error("[AdminMahasiswaController@destroy] QueryException saat menghapus mahasiswa (NIM: {$mahasiswa->nim}): " . $errorMessage, ['errorInfo' => $e->errorInfo, 'trace' => $e->getTraceAsString()]);
            // Cek kode error SQL untuk foreign key constraint violation (misal 1451 untuk MySQL)
            if (str_contains(strtolower($errorMessage), 'foreign key constraint') || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451) ) {
                 return back()->with('error', 'Gagal menghapus mahasiswa. Masih ada data terkait yang aktif (misalnya pengajuan judul, bimbingan, dokumen). Harap periksa dan hapus data terkait tersebut terlebih dahulu, atau konfigurasi database untuk menghapus secara cascade.');
            }
            return back()->with('error', 'Gagal menghapus mahasiswa karena masalah database. Silakan coba lagi. Detail: ' . $errorMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[AdminMahasiswaController@destroy] Exception umum saat menghapus mahasiswa (NIM: {$mahasiswa->nim}): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan umum saat menghapus mahasiswa. Silakan coba lagi. Detail: ' . $e->getMessage());
        }
    }
}
