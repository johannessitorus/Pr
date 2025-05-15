<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    protected $fillable = [
        'user_id',
        'nidn',
        'prodi_id',
        'spesialisasi',
    ];

    // RELASI

    /**
     * Get the user that owns the dosen.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the prodi that owns the dosen.
     */
    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    // Anda bisa menambahkan relasi lain di sini jika dosen memiliki banyak mahasiswa bimbingan secara langsung
    // Namun, biasanya ini didapat melalui tabel User (dosen_pembimbing_id di Mahasiswa) atau HistoryBimbingan
}
