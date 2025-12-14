<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Year extends Model
{
    protected $fillable = [
        'code',
        'name',
        'smt',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function alumni(): HasMany
    {
        return $this->hasMany(Alumni::class, 'tahun_id');
    }
}
