<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    protected $fillable = [
        'year_id',
        'title',
        'description',
        'is_mandatory',
        'is_active',
        'is_public',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class, 'year_id');
    }

    public function prodis(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Prodi::class, 'questionnaire_prodi', 'questionnaire_id', 'prodi_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('section')->orderBy('order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function isOpen(): bool
    {
        $now = now()->toDateString();
        $isStarted = is_null($this->start_date) || $this->start_date->toDateString() <= $now;
        $isNotEnded = is_null($this->end_date) || $this->end_date->toDateString() >= $now;
        
        return $this->is_active && $isStarted && $isNotEnded;
    }

    public function scopeActive($query)
    {
        return $query->where('questionnaires.is_active', true);
    }

    public function scopeOpen($query)
    {
        $now = now()->toDateString();
        return $query->active()
            ->where(function ($q) use ($now) {
                $q->whereNull('questionnaires.start_date')
                  ->orWhere('questionnaires.start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('questionnaires.end_date')
                  ->orWhere('questionnaires.end_date', '>=', $now);
            });
    }
}
