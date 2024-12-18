<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'xid',
        'name',
        'type',
        'description',
        'created_by',
        'is_archived',
        'archived_at'
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(ConversationMessage::class)->latest();
    }

