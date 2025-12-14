<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuestionnaireController;
use App\Http\Controllers\Api\AlumniController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\ProdiController;
use App\Http\Controllers\Api\YearController;
use App\Http\Controllers\Api\QuestionnaireTypeController;
use App\Http\Controllers\Api\QuestionnaireManagementController;
use App\Http\Controllers\Api\QuestionManagementController;
use App\Http\Controllers\Api\FacultyController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Public data
    Route::get('/prodis', [ProdiController::class, 'index']);

    // Alumni routes
    Route::middleware(['role:alumni'])->group(function () {
        Route::get('/questionnaires/counts', [QuestionnaireController::class, 'counts']);
        Route::get('/questionnaires', [QuestionnaireController::class, 'index']);
        Route::get('/questionnaires/{id}', [QuestionnaireController::class, 'show']);
        Route::post('/questionnaires/{id}/submit', [QuestionnaireController::class, 'submit'])
            ->middleware('throttle:5,1'); // Rate limit: 5 requests per minute
    });

    // Admin routes
    Route::prefix('admin')->middleware(['role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/alumni', [AlumniController::class, 'index']);
        Route::post('/alumni', [AlumniController::class, 'store']);
        Route::get('/alumni/{id}', [AlumniController::class, 'show']);
        Route::put('/alumni/{id}', [AlumniController::class, 'update']);
        Route::delete('/alumni/{id}', [AlumniController::class, 'destroy']);
        Route::post('/alumni/import', [AlumniController::class, 'import']);
        Route::get('/alumni/template', [AlumniController::class, 'downloadTemplate']);
        Route::get('/export/excel', [ExportController::class, 'excel']);
        Route::get('/export/pdf', [ExportController::class, 'pdf']);

        // Questionnaire Type Management
        Route::get('/questionnaire-types', [QuestionnaireTypeController::class, 'index']);
        Route::post('/questionnaire-types', [QuestionnaireTypeController::class, 'store']);
        Route::put('/questionnaire-types/{id}', [QuestionnaireTypeController::class, 'update']);
        Route::delete('/questionnaire-types/{id}', [QuestionnaireTypeController::class, 'destroy']);

        // Questionnaire Management
        Route::get('/questionnaires', [QuestionnaireManagementController::class, 'index']);
        Route::post('/questionnaires', [QuestionnaireManagementController::class, 'store']);
        Route::get('/questionnaires/{id}', [QuestionnaireManagementController::class, 'show']);
        Route::put('/questionnaires/{id}', [QuestionnaireManagementController::class, 'update']);
        Route::delete('/questionnaires/{id}', [QuestionnaireManagementController::class, 'destroy']);
        Route::get('/questionnaires/{id}/results', [QuestionnaireManagementController::class, 'results']);
        Route::get('/questionnaires/{id}/export/excel', [QuestionnaireManagementController::class, 'exportExcel']);
        Route::get('/questionnaires/{id}/export/pdf', [QuestionnaireManagementController::class, 'exportPdf']);

        // Question Management
        Route::post('/questionnaires/{questionnaireId}/questions', [QuestionManagementController::class, 'store']);
        Route::put('/questions/{id}', [QuestionManagementController::class, 'update']);
        Route::delete('/questions/{id}', [QuestionManagementController::class, 'destroy']);
        Route::post('/questionnaires/{questionnaireId}/questions/reorder', [QuestionManagementController::class, 'reorder']);

        // Faculty Routes
        Route::apiResource('faculties', FacultyController::class);

        // Prodi Routes
        Route::apiResource('prodis', ProdiController::class);

        // Year Routes
        Route::apiResource('years', YearController::class);
        // Override index/public access if needed or keep it protected but accessible
        // Since prodis public endpoint is already defined above as 'prodis', we might need to adjust.
        // The request says "create api and view complete CRUD".
        // The public 'prodis' endpoint uses 'index'.
        // So here we can just add the rest: store, show, update, destroy.
        // But apiResource does all. Let's just use it, but maybe prefix or handle appropriately.
        // Actually, the public one is `Route::get('/prodis', [ProdiController::class, 'index']);`
        // We can keep that public. And here we add the admin ones.
        // Since apiResource creates 'index' as 'prodis.index', it might conflict if names are used, but routing wise:
        // Public: GET /prodis
        // Admin: GET /admin/prodis (if we put it here inside admin prefix).
        // Yes, these are inside 'admin' prefix. So GET /admin/prodis, POST /admin/prodis etc.
    });
});
