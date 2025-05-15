<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'user_id',
        'nim',
        'prodi_id',
        'angkatan',
        'nomor_kelompok',
        'dosen_pembimbing_id',
        'judul_proyek_akhir',
        'status_proyek_akhir',
    ];

    protected $casts = [
        'angkatan' => 'integer', // Atau biarkan string jika 'year' MySQL
    ];

    // RELASI

    /**
     * Get the user that owns the mahasiswa.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the prodi that owns the mahasiswa.
     */
    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    /**
     * Get the dosen pembimbing (user) for the mahasiswa.
     */
    public function dosenPembimbing()
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id');
    }

    /**
     * Get the request judul for the mahasiswa.
     */
    public function requestJudul()
    {
        return $this->hasMany(RequestJudul::class);
    }

    /**
     * Get the request bimbingan for the mahasiswa.
     */
    public function requestBimbingan()
    {
        return $this->hasMany(RequestBimbingan::class);
    }

    /**
     * Get the history bimbingan for the mahasiswa.
     */
    public function historyBimbingan()
    {
        return $this->hasMany(HistoryBimbingan::class);
    }

    /**
     * Get the dokumen proyek akhir for the mahasiswa.
     */
    public function dokumenProyekAkhir()
    {
        return $this->hasMany(DokumenProyekAkhir::class);
    }

     /**
     * Get the log activities related to this mahasiswa.
     */
    public function logActivities()
    {
        return $this->hasMany(LogActivity::class, 'mahasiswa_terkait_id');
    }
}
