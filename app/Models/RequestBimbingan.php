<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestBimbingan extends Model
{
    use HasFactory;

    protected $table = 'request_bimbingan';

    protected $fillable = [
        'mahasiswa_id',
        'dosen_id',
        'tanggal_usulan',
        'jam_usulan',
        'lokasi_usulan',
        'topik_bimbingan',
        'status_request',
        'catatan',
        'catatan_dosen',   // ditambahkan
        'tanggal_dosen',   // ditambahkan
        'jam_dosen',       // ditambahkan
    ];

    protected $casts = [
        'tanggal_usulan' => 'date',
    ];

    // RELASI

    /**
     * Get the mahasiswa that owns the request bimbingan.
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Get the dosen (user) for the request bimbingan.
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Get the history bimbingan record associated with this request (if any).
     */
    public function historyBimbingan()
    {
        return $this->hasOne(HistoryBimbingan::class); // Satu request bisa jadi satu history bimbingan
    }
}
