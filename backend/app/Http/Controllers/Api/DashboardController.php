<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Response;
use App\Models\Questionnaire;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Base query for responses
        $responseQuery = Response::query();

        // Apply filters
        if ($request->filled('prodi_id')) {
            $responseQuery->whereHas('alumni', function ($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        if ($request->filled('tahun_lulus')) {
            $responseQuery->whereHas('alumni', function ($q) use ($request) {
                $q->where('tahun_lulus', $request->tahun_lulus);
            });
        }

        if ($request->filled('questionnaire_id')) {
            $responseQuery->where('questionnaire_id', $request->questionnaire_id);
        }

        if ($request->filled('periode_tahun')) {
            $responseQuery->whereHas('questionnaire', function ($q) use ($request) {
                $q->where('periode_tahun', $request->periode_tahun);
            });
        }

        // Statistics
        $totalAlumni = Alumni::count();
        $totalResponses = (clone $responseQuery)->count();
        $activeQuestionnaires = Questionnaire::open()->count();

        // Response per prodi
        $responsePerProdi = Prodi::withCount(['alumni as total_alumni'])
            ->get()
            ->map(function ($prodi) use ($request) {
                $responseCount = Response::whereHas('alumni', function ($q) use ($prodi) {
                    $q->where('prodi_id', $prodi->prodi_id);
                })->when($request->filled('questionnaire_id'), function ($q) use ($request) {
                    $q->where('questionnaire_id', $request->questionnaire_id);
                })->count();

                return [
                    'prodi_id' => $prodi->prodi_id,
                    'nama' => $prodi->nama_prodi,
                    'total_alumni' => $prodi->total_alumni,
                    'total_responses' => $responseCount,
                    'percentage' => $prodi->total_alumni > 0
                        ? round(($responseCount / $prodi->total_alumni) * 100, 1)
                        : 0,
                ];
            });

        // Response per tahun lulus
        $responsePerTahun = Alumni::select('tahun_lulus')
            ->selectRaw('COUNT(*) as total_alumni')
            ->groupBy('tahun_lulus')
            ->orderBy('tahun_lulus', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($row) use ($request) {
                $responseCount = Response::whereHas('alumni', function ($q) use ($row) {
                    $q->where('tahun_lulus', $row->tahun_lulus);
                })->when($request->filled('questionnaire_id'), function ($q) use ($request) {
                    $q->where('questionnaire_id', $request->questionnaire_id);
                })->count();

                return [
                    'tahun' => $row->tahun_lulus,
                    'total_alumni' => $row->total_alumni,
                    'total_responses' => $responseCount,
                    'percentage' => $row->total_alumni > 0
                        ? round(($responseCount / $row->total_alumni) * 100, 1)
                        : 0,
                ];
            });

        // Questionnaire stats - limit removed, will be paginated in frontend
        $questionnaireStats = Questionnaire::withCount('responses')
            ->orderBy('periode_tahun', 'desc')
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'title' => $q->title,
                    'periode' => $q->periode_tahun,
                    'responses_count' => $q->responses_count,
                    'is_active' => $q->isOpen(),
                ];
            });

        return response()->json([
            'summary' => [
                'total_alumni' => $totalAlumni,
                'total_responses' => $totalResponses,
                'active_questionnaires' => $activeQuestionnaires,
                'response_rate' => $totalAlumni > 0
                    ? round(($totalResponses / $totalAlumni) * 100, 1)
                    : 0,
            ],
            'per_prodi' => $responsePerProdi,
            'per_tahun' => $responsePerTahun,
            'questionnaires' => $questionnaireStats,
        ]);
    }
}
