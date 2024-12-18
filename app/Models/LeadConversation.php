<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Lead;

class LeadConversation extends Model
{
    protected $fillable = [
        'lead_id',
        'message_sid',
        'direction',
        'message',
        'status',
        'from',
        'to',
        'delivered_at',
        'read_at'
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
