<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'username',
        'password',
        'role_id',
        'alumni_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function alumni(): HasOne
    {
        return $this->hasOne(Alumni::class, 'user_id', 'id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_id === Role::SUPERADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role_id, [Role::SUPERADMIN, Role::ADMIN]);
    }

    public function isAlumni(): bool
    {
        return $this->role_id === Role::ALUMNI;
    }
}
