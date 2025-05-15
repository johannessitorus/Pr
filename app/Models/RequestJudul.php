<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestJudul extends Model
{
    use HasFactory;

    protected $table = 'request_judul';

    protected $fillable = [
        'mahasiswa_id',
        'dosen_tujuan_id',
        'judul_diajukan',
        'deskripsi',
        'status',
        'catatan_dosen',
    ];

    // RELASI

    /**
     * Get the mahasiswa that owns the request judul.
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Get the dosen tujuan (user) for the request judul.
     */
    public function dosenTujuan()
    {
        return $this->belongsTo(Dosen::class, 'dosen_tujuan_id');
    }
}
