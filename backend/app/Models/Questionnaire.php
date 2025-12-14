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
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
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
        return $this->is_active
            && $this->start_date <= $now
            && $this->end_date >= $now;
    }

    public function scopeActive($query)
    {
        return $query->where('questionnaires.is_active', true);
    }

    public function scopeOpen($query)
    {
        $now = now()->toDateString();
        return $query->active()
            ->where('questionnaires.start_date', '<=', $now)
            ->where('questionnaires.end_date', '>=', $now);
    }
}
