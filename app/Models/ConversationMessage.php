<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConversationMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'xid',
        'conversation_id',
        'sender_id',
        'content',
        'type',
        'metadata',
        'delivered_at',
        'read_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reactions()
    {
        return $this->hasMany(ConversationMessageReaction::class, 'message_id');
    }

    public function attachments()
    {
        return $this->hasMany(ConversationAttachment::class, 'message_id');
    }
}
