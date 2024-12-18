<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConversationParticipant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'xid',
        'conversation_id',
        'user_id',
        'role',
        'last_read_at',
        'is_muted'
    ];

    protected $casts = [
        'is_muted' => 'boolean',
        'last_read_at' => 'datetime'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unreadMessages()
    {
        return $this->conversation->messages()
            ->where('created_at', '>', $this->last_read_at ?? '1970-01-01')
            ->where('sender_id', '!=', $this->user_id);
    }
}
