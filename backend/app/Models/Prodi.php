<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prodi extends Model
{
    protected $fillable = ['faculty_id', 'name', 'code', 'strata'];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function alumni(): HasMany
    {
        return $this->hasMany(Alumni::class, 'prodi_id');
    }
}
