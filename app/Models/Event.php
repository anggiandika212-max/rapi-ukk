<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'room_id',
        'user_id',
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'status',
        'approved_by',
        'move_reason',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    /**
     * Event belongs to Room.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Event belongs to User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}