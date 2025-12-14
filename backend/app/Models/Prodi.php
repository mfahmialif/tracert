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

    public function questionnaires(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Questionnaire::class, 'questionnaire_prodi', 'prodi_id', 'questionnaire_id');
    }
}
