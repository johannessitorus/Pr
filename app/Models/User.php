<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes; // << TAMBAHKAN INI

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes; // << TAMBAHKAN SoftDeletes

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Otomatis hash saat diset (Laravel 9+)
    ];

    // RELASI
    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class, 'user_id');
    }

    public function dosen()
    {
        return $this->hasOne(Dosen::class, 'user_id');
    }

    public function requestJudulDiterima()
    {
        return $this->hasMany(RequestJudul::class, 'dosen_tujuan_id');
    }

    public function requestBimbinganDiterima()
    {
        return $this->hasMany(RequestBimbingan::class, 'dosen_id');
    }

    public function bimbinganDiberikan()
    {
        return $this->hasMany(HistoryBimbingan::class, 'dosen_id');
    }

    public function dokumenDireview()
    {
        return $this->hasMany(DokumenProyekAkhir::class, 'reviewed_by');
    }

    public function forums()
    {
        return $this->hasMany(Forum::class, 'user_id');
    }

    public function forumMessages()
    {
        return $this->hasMany(ForumMessage::class, 'user_id');
    }

    public function logActivities()
    {
        return $this->hasMany(LogActivity::class, 'user_id');
    }

    // HELPER METHODS (Opsional tapi berguna)
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    /**
     * Get the human-readable role name.
     */
    public function getRoleNameAttribute(): string
    {
        return ucfirst($this->role);
    }
}
