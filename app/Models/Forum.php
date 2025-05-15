<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    use HasFactory;

    protected $table = 'forums';

    protected $fillable = [
        'user_id',
        'judul_forum',
        'deskripsi',
    ];

    // RELASI

    /**
     * Get the user that created the forum.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the messages for the forum.
     */
    public function messages()
    {
        return $this->hasMany(ForumMessage::class);
    }
}
