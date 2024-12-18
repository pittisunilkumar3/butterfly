<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConversationAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'xid',
        'message_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size'
    ];

    public function message()
    {
        return $this->belongsTo(ConversationMessage::class, 'message_id');
    }
}
