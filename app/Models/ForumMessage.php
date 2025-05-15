<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumMessage extends Model
{
    use HasFactory;

    protected $table = 'forum_messages';

    protected $fillable = [
        'forum_id',
        'user_id',
        'parent_message_id',
        'isi_pesan',
    ];

    // RELASI

    /**
     * Get the forum that the message belongs to.
     */
    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    /**
     * Get the user that created the message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent message (if this is a reply).
     */
    public function parentMessage()
    {
        return $this->belongsTo(ForumMessage::class, 'parent_message_id');
    }

    /**
     * Get the replies to this message.
     */
    public function replies()
    {
        return $this->hasMany(ForumMessage::class, 'parent_message_id');
    }
}
