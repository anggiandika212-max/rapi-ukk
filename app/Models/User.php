<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Role;
use App\Models\Room;
use App\Models\Event;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean'
        ];
    }

    /**
     * User memiliki satu Role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id' );
    }

    /**
     * User sebagai PIC memiliki banyak Room
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'pic_id');
    }

    /**
     * User/Guru memiliki banyak Event
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'user_id');
    }
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'reporter_id');
    }
}