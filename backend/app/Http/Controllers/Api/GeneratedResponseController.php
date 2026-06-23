<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Services\QuestionnaireResponseGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class GeneratedResponseController extends Controller
{
    public function store(Request $request, Questionnaire $questionnaire, QuestionnaireResponseGenerator $generator)
    {
        $validated = $request->validate([
            'respondent_count' => 'required|integer|min:1|max:1000',
            'replace_generated' => 'sometimes|boolean',
            'submitted_from' => 'required|date',
            'submitted_until' => 'required|date|after_or_equal:submitted_from',
            'distributions' => 'present|array',
            'distributions.*.question_id' => 'required|integer|distinct',
            'distributions.*.option_counts' => 'required|array',
            'distributions.*.option_counts.*.option' => 'present|string',
            'distributions.*.option_counts.*.count' => 'present|integer|min:0',
        ]);

        $count = $generator->generate(
            $questionnaire,
            $request->user(),
            $validated['respondent_count'],
            $validated['distributions'],
            $validated['replace_generated'] ?? true,
            CarbonImmutable::parse($validated['submitted_from'])->startOfDay(),
            CarbonImmutable::parse($validated['submitted_until'])->endOfDay(),
        );

        return response()->json([
            'message' => "{$count} responden simulasi berhasil dibuat.",
            'generated_count' => $count,
        ], 201);
    }
}
