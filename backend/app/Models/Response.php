<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Response extends Model
{
    protected $fillable = [
        'questionnaire_id',
        'alumni_id',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class, 'alumni_id', 'alumni_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
