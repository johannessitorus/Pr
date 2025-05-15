<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;

    protected $table = 'log_activities';

    // Log biasanya tidak di-update, jadi updated_at tidak relevan
    const UPDATED_AT = null;
    // created_at sudah didefinisikan di migrasi dengan useCurrent()

    protected $fillable = [
        'user_id',
        'mahasiswa_terkait_id',
        'dosen_terkait_id',
        'activity_type',
        'description',
        'ip_address',
        'user_agent',
        // 'created_at' akan diisi otomatis oleh database jika useCurrent()
    ];

    // RELASI

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the mahasiswa related to this activity.
     */
    public function mahasiswaTerkait()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_terkait_id');
    }

    /**
     * Get the dosen related to this activity.
     */
    public function dosenTerkait()
    {
        return $this->belongsTo(Dosen::class, 'dosen_terkait_id');
    }
}
