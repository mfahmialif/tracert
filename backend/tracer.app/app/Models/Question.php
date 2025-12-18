<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'questionnaire_id',
        'question_text',
        'type',
        'options',
        'is_required',
        'order',
        'section',
        'depends_on',
        'depends_value',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function dependsOnQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'depends_on');
    }

    public function dependentQuestions(): HasMany
    {
        return $this->hasMany(Question::class, 'depends_on');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
