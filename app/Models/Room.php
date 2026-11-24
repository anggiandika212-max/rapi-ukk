<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Room extends Model
{
    protected $fillable = [
        'pic_id',
        'room_name',
        'facilities',
        'facility_condition',
        'status',
        'photo',
        'is_active'
    ];
    protected $casts= [
        'is_active' => 'boolean',
    ];

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'room_id');
    }
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}