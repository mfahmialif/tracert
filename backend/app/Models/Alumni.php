<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alumni extends Model
{
    protected $table = 'alumni';
    protected $primaryKey = 'alumni_id';

    protected $fillable = [
        'user_id',
        'nim',
        'nama',
        'prodi_id',
        'tahun_id',
        'email',
        'no_hp',
        'status',
    ];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class, 'tahun_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class, 'alumni_id', 'alumni_id');
    }
}
