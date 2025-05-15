<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    use HasFactory;

    protected $table = 'prodi'; // Nama tabel eksplisit

    public $timestamps = false; // Tidak menggunakan timestamps created_at, updated_at

    protected $fillable = [
        'kode_prodi',
        'nama_prodi',
        'fakultas',
    ];

    // RELASI

    /**
     * Get the mahasiswa for the prodi.
     */
    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    /**
     * Get the dosen for the prodi.
     */
    public function dosen()
    {
        return $this->hasMany(Dosen::class);
    }
}
