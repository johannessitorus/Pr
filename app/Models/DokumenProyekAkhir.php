<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenProyekAkhir extends Model
{
    use HasFactory;

    protected $table = 'dokumen_proyek_akhir';

    /**
     * Indicates if the model should be timestamped.
     * true adalah default, jadi baris ini opsional jika Anda ingin timestamps.
     * Eloquent akan secara otomatis mengelola kolom 'created_at' dan 'updated_at'.
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mahasiswa_id',
        'jenis_dokumen_id',
        'nama_file_asli',    // Saya ganti dari 'nama_file' untuk konsistensi dengan controller
        'nama_file_unik',    // Menambahkan ini untuk nama file yang disimpan di server
        'file_path',
        'ekstensi_file',     // Menambahkan ini untuk menyimpan ekstensi
        'ukuran_file',       // Menambahkan ini untuk menyimpan ukuran file
        'versi',
        'catatan_mahasiswa', // Saya ganti dari 'catatan' untuk lebih spesifik
        'status_review',
        'reviewed_by',
        'reviewed_at',
        'catatan_reviewer',  // Menambahkan ini untuk catatan dari reviewer
        // 'uploaded_at' TIDAK PERLU DI SINI JIKA ANDA MENGGUNAKAN TIMESTAMPS STANDAR (created_at akan berfungsi sebagai uploaded_at)
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'versi' => 'integer',
        'ukuran_file' => 'integer', // Casting untuk ukuran file
        'reviewed_at' => 'datetime',
        // 'uploaded_at' => 'datetime', // Tidak perlu jika created_at digunakan sebagai waktu upload
    ];

    // RELASI

    /**
     * Get the mahasiswa that owns the dokumen proyek akhir.
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Get the jenis dokumen for the dokumen proyek akhir.
     */
    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class);
    }

    /**
     * Get the user (dosen/admin) who reviewed the dokumen proyek akhir.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
