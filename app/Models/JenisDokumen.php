<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisDokumen extends Model
{
    use HasFactory;

    protected $table = 'jenis_dokumen';

    public $timestamps = false; // Tidak menggunakan timestamps

    protected $fillable = [
        'nama_jenis',
        'deskripsi',
    ];

    // RELASI

    /**
     * Get the dokumen proyek akhir for the jenis dokumen.
     */
    public function dokumenProyekAkhir()
    {
        return $this->hasMany(DokumenProyekAkhir::class);
    }
}
