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
        'respondent_name',
        'respondent_email',
        'respondent_phone',
        'is_generated',
        'generated_by',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_generated' => 'boolean',
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
