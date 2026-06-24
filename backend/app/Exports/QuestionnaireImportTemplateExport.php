<?php

namespace App\Exports;

use App\Models\Questionnaire;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class QuestionnaireImportTemplateExport implements FromArray, WithHeadings, WithEvents
{
    protected $questionnaire;
    protected $questions;

    public function __construct($questionnaireId)
    {
        $this->questionnaire = Questionnaire::with('questions')->findOrFail($questionnaireId);
        $this->questions = $this->questionnaire->questions()->orderBy('section')->orderBy('order')->get();
    }

    public function headings(): array
    {
        $headers = $this->questionnaire->is_public
            ? ['Nama Responden', 'Email', 'No. Telepon', 'Tanggal Submit (YYYY-MM-DD HH:MM:SS)']
            : ['NIM', 'Nama Lengkap (Opsional)', 'Tanggal Submit (YYYY-MM-DD HH:MM:SS)'];

        foreach ($this->questions as $question) {
            $headers[] = $question->question_text;
        }

        return $headers;
    }

    public function array(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Freeze the first row
                $sheet->freezePane('A2');

                // Determine base offset for question columns
                $baseOffset = $this->questionnaire->is_public ? 5 : 4; // 1-based index (A=1)
                
                // Start writing options to hidden columns far away to bypass 255 char limit formula
                $hiddenColIndex = count($this->headings()) + 5; 

                foreach ($this->questions as $index => $question) {
                    $colLetter = Coordinate::stringFromColumnIndex($baseOffset + $index);
                    
                    if (in_array($question->type, ['radio', 'checkbox', 'select', 'scale'])) {
                        $options = $question->options ?? [];
                        if (empty($options)) continue;

                        $optColLetter = Coordinate::stringFromColumnIndex($hiddenColIndex);
                        $sheet->getColumnDimension($optColLetter)->setVisible(false);
                        
                        foreach ($options as $optIdx => $opt) {
                            $sheet->setCellValue($optColLetter . ($optIdx + 1), $opt);
                        }
                        
                        $formula = $optColLetter . '1:' . $optColLetter . count($options);
                        
                        $validation = $sheet->getCell($colLetter . '2')->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(false); // Allow multiple comma-separated inputs
                        $validation->setShowDropDown(true);
                        $validation->setFormula1($formula);
                        
                        // Apply validation to rows 2 to 1000
                        for ($i = 2; $i <= 1000; $i++) {
                            $sheet->getCell($colLetter . $i)->setDataValidation(clone $validation);
                        }
                        
                        $hiddenColIndex++;
                    }
                }
            },
        ];
    }
}
