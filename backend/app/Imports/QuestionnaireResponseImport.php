<?php

namespace App\Imports;

use App\Models\Alumni;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Response;
use App\Models\Answer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class QuestionnaireResponseImport implements ToCollection
{
    protected $questionnaire;
    protected $questions;

    public function __construct($questionnaireId)
    {
        $this->questionnaire = Questionnaire::with('questions')->findOrFail($questionnaireId);
        $this->questions = $this->questionnaire->questions()->orderBy('section')->orderBy('order')->get();
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) return;

        $headers = $rows->first()->toArray();
        // Map question text to column index
        $questionIndexMap = [];
        foreach ($this->questions as $question) {
            $index = array_search($question->question_text, $headers);
            if ($index !== false) {
                $questionIndexMap[$question->id] = $index;
            }
        }

        $nimIndex = array_search('NIM', $headers);
        $namaPubIndex = array_search('Nama Responden', $headers);
        $emailIndex = array_search('Email', $headers);
        $phoneIndex = array_search('No. Telepon', $headers);
        
        // Find Date column
        $dateIndex = false;
        foreach ($headers as $idx => $header) {
            if (str_contains(strtolower((string)$header), 'tanggal submit')) {
                $dateIndex = $idx;
                break;
            }
        }

        DB::beginTransaction();
        try {
            $isFirstRow = true;
            foreach ($rows as $row) {
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue; // Skip header row
                }

                $isPublic = $this->questionnaire->is_public;
                
                $alumniId = null;
                $respondentName = null;
                $respondentEmail = null;
                $respondentPhone = null;

                if ($isPublic) {
                    if ($namaPubIndex === false || !isset($row[$namaPubIndex]) || trim((string)$row[$namaPubIndex]) === '') {
                        continue;
                    }
                    $respondentName = trim((string)$row[$namaPubIndex]);
                    $respondentEmail = $emailIndex !== false && isset($row[$emailIndex]) ? trim((string)$row[$emailIndex]) : null;
                    $respondentPhone = $phoneIndex !== false && isset($row[$phoneIndex]) ? trim((string)$row[$phoneIndex]) : null;
                } else {
                    if ($nimIndex === false || !isset($row[$nimIndex]) || trim((string)$row[$nimIndex]) === '') {
                        continue;
                    }
                    $nim = trim((string)$row[$nimIndex]);
                    $alumni = Alumni::where('nim', $nim)->first();
                    if (!$alumni) {
                        continue;
                    }
                    $alumniId = $alumni->alumni_id;
                    
                    $existing = Response::where('questionnaire_id', $this->questionnaire->id)
                        ->where('alumni_id', $alumniId)
                        ->first();
                        
                    if ($existing) {
                        continue;
                    }
                }

                $submittedAt = now();
                if ($dateIndex !== false && isset($row[$dateIndex]) && !empty($row[$dateIndex])) {
                    try {
                        if (is_numeric($row[$dateIndex])) {
                            $submittedAt = Date::excelToDateTimeObject($row[$dateIndex]);
                        } else {
                            $submittedAt = Carbon::parse($row[$dateIndex]);
                        }
                    } catch (\Exception $e) {
                        // ignore
                    }
                }

                $response = Response::create([
                    'questionnaire_id' => $this->questionnaire->id,
                    'alumni_id' => $alumniId,
                    'respondent_name' => $respondentName,
                    'respondent_email' => $respondentEmail,
                    'respondent_phone' => $respondentPhone,
                    'is_generated' => false,
                    'submitted_at' => $submittedAt,
                ]);

                foreach ($this->questions as $question) {
                    if (isset($questionIndexMap[$question->id])) {
                        $idx = $questionIndexMap[$question->id];
                        $val = isset($row[$idx]) ? $row[$idx] : null;
                        
                        if ($val !== null && trim((string)$val) !== '') {
                            $valStr = trim((string)$val);
                            
                            if ($question->type === 'checkbox') {
                                $arr = array_map('trim', explode(',', $valStr));
                                $valStr = json_encode($arr);
                            }
                            
                            Answer::create([
                                'response_id' => $response->id,
                                'question_id' => $question->id,
                                'answer_value' => $valStr,
                            ]);
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
