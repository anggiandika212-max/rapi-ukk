<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    public const STATUS_BELUM_DIPERBAIKI = 'Belum Diperbaiki';
    public const STATUS_MENUNGGU_SPARE_PART = 'Menunggu Spare Part';
    public const STATUS_SUDAH_DIPERBAIKI = 'Sudah Diperbaiki';
    protected $fillable = [
        'room_id',
        'reporter_id',
        'description',
        'photo_path',
        'status_repair',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}