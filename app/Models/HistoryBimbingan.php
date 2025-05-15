<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryBimbingan extends Model
{
    use HasFactory;

    protected $table = 'history_bimbingan';

    protected $fillable = [
        'mahasiswa_id',
        'dosen_id',
        'request_bimbingan_id',
        'tanggal_bimbingan',
        'topik',
        'catatan_mahasiswa',
        'catatan_dosen',
        'pertemuan_ke',
        'status_kehadiran',
    ];

    protected $casts = [
        'tanggal_bimbingan' => 'datetime',
        'pertemuan_ke' => 'integer',
    ];

    // RELASI

    /**
     * Get the mahasiswa that owns the history bimbingan.
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Get the dosen (user) for the history bimbingan.
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Get the request bimbingan that this history bimbingan originated from.
     */
    public function requestBimbingan()
    {
        return $this->belongsTo(RequestBimbingan::class);
    }
}
