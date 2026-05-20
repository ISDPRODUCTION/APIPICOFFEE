<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'avatar',
        'employee_id',
        'shift_started_at',
        'theme_color',      
        'dark_mode',        
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'shift_started_at'  => 'datetime',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'dark_mode'         => 'boolean', // ← tambahan
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return \App\Support\StorageUrl::public($this->avatar)
                ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=F97316&color=fff';
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=F97316&color=fff';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function getShiftStatusAttribute(): string
    {
        if ($this->shift_started_at) {
            return 'Active since ' . $this->shift_started_at->format('H:i A');
        }
        return 'No active shift';
    }
}