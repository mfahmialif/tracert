<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Response;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuestionnaireResponseGenerator
{
    private const FIRST_NAMES = [
        'Ahmad', 'Muhammad', 'Abdul', 'Fajar', 'Rizky', 'Dimas', 'Bagus', 'Arif', 'Bayu', 'Rafi',
        'Siti', 'Nur', 'Aisyah', 'Dewi', 'Putri', 'Aulia', 'Nabila', 'Fitri', 'Lestari', 'Rahma',
    ];

    private const LAST_NAMES = [
        'Pratama', 'Saputra', 'Ramadhan', 'Hidayat', 'Nugroho', 'Santoso', 'Maulana', 'Firmansyah',
        'Kurniawan', 'Setiawan', 'Permata', 'Safitri', 'Maharani', 'Anggraini', 'Wulandari',
        'Puspitasari', 'Oktaviani', 'Amalia', 'Rahmawati', 'Ningsih',
    ];

    private const CITIES = [
        'Surabaya', 'Malang', 'Pasuruan', 'Sidoarjo', 'Probolinggo', 'Jember', 'Banyuwangi',
        'Jakarta', 'Bandung', 'Semarang', 'Yogyakarta', 'Solo', 'Makassar', 'Banjarmasin', 'Mataram',
    ];

    private const JOBS = [
        'Guru', 'Staf Administrasi', 'Wirausaha', 'Karyawan Swasta', 'Pegawai Pemerintah',
        'Konsultan', 'Peneliti', 'Tenaga Kependidikan', 'Penyuluh', 'Pengelola Lembaga Pendidikan',
    ];

    private const COMPANIES = [
        'PT Nusantara Sejahtera', 'CV Karya Mandiri', 'Yayasan Pendidikan Harapan',
        'Kantor Pemerintah Daerah', 'Sekolah Islam Terpadu', 'Universitas Nusantara',
        'Pesantren Al-Hikmah', 'PT Mitra Digital Indonesia', 'Lembaga Sosial Masyarakat',
    ];

    private const LONG_TEXTS = [
        'Pelayanan sudah baik dan perlu dipertahankan dengan peningkatan komunikasi kepada alumni.',
        'Materi pembelajaran relevan, namun praktik lapangan dan jaringan mitra masih dapat diperluas.',
        'Secara umum pengalaman selama studi sangat membantu dalam menghadapi dunia kerja.',
        'Sistem informasi perlu dibuat lebih cepat, sederhana, dan mudah diakses melalui perangkat seluler.',
        'Pendampingan karier dan informasi lowongan kerja sebaiknya diberikan secara rutin kepada alumni.',
    ];

    public function generate(
        Questionnaire $questionnaire,
        User $actor,
        int $respondentCount,
        array $distributions,
        bool $replaceGenerated,
        CarbonImmutable $submittedFrom,
        CarbonImmutable $submittedUntil,
    ): int {
        $questions = $questionnaire->questions()->get();
        $distributionMap = collect($distributions)->keyBy('question_id');

        $this->validateDistributions($questions, $distributionMap, $respondentCount);

        return DB::transaction(function () use (
            $questionnaire,
            $actor,
            $respondentCount,
            $distributionMap,
            $questions,
            $replaceGenerated,
            $submittedFrom,
            $submittedUntil,
        ) {
            if ($replaceGenerated) {
                Response::where('questionnaire_id', $questionnaire->id)
                    ->where('is_generated', true)
                    ->delete();
            }

            $identities = $questionnaire->is_public
                ? $this->makePublicIdentities($questionnaire->id, $respondentCount)
                : $this->makeAlumniIdentities($questionnaire, $respondentCount, $replaceGenerated);
            $answersByQuestion = [];

            foreach ($questions as $question) {
                $distribution = $distributionMap->get($question->id);
                $answersByQuestion[$question->id] = $this->makeQuestionAnswers(
                    $question,
                    $respondentCount,
                    $distribution['option_counts'] ?? [],
                    $identities,
                );
            }

            $answerRows = [];
            $now = now();

            foreach ($identities as $index => $identity) {
                $submittedAt = $this->randomDateTime($submittedFrom, $submittedUntil);
                $response = Response::create([
                    'questionnaire_id' => $questionnaire->id,
                    'alumni_id' => $identity['alumni_id'],
                    'respondent_name' => $questionnaire->is_public ? $identity['name'] : null,
                    'respondent_email' => $questionnaire->is_public ? $identity['email'] : null,
                    'respondent_phone' => $questionnaire->is_public ? $identity['phone'] : null,
                    'is_generated' => true,
                    'generated_by' => $actor->id,
                    'submitted_at' => $submittedAt,
                    'created_at' => $submittedAt,
                    'updated_at' => $submittedAt,
                ]);

                foreach ($questions as $question) {
                    $answerRows[] = [
                        'response_id' => $response->id,
                        'question_id' => $question->id,
                        'answer_value' => $answersByQuestion[$question->id][$index],
                        'created_at' => $submittedAt ?? $now,
                        'updated_at' => $submittedAt ?? $now,
                    ];
                }
            }

            foreach (array_chunk($answerRows, 1000) as $chunk) {
                Answer::insert($chunk);
            }

            return $respondentCount;
        });
    }

    private function validateDistributions(Collection $questions, Collection $distributionMap, int $respondentCount): void
    {
        $questionIds = $questions->pluck('id');
        $unknownQuestionIds = $distributionMap->keys()->diff($questionIds);

        if ($unknownQuestionIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'distributions' => 'Terdapat konfigurasi untuk pertanyaan yang bukan bagian dari kuesioner ini.',
            ]);
        }

        foreach ($questions as $question) {
            if (! in_array($question->type, ['radio', 'select', 'scale', 'checkbox'], true)) {
                continue;
            }

            $options = array_values($question->options ?? []);
            $optionCounts = $distributionMap->get($question->id)['option_counts'] ?? null;

            if ($optionCounts === null) {
                throw ValidationException::withMessages([
                    "distributions.{$question->id}" => 'Jumlah setiap pilihan wajib diatur.',
                ]);
            }

            $submittedOptions = collect($optionCounts)->pluck('option');
            if ($submittedOptions->duplicates()->isNotEmpty() || $submittedOptions->sort()->values()->all() !== collect($options)->sort()->values()->all()) {
                throw ValidationException::withMessages([
                    "distributions.{$question->id}" => 'Daftar pilihan tidak sesuai dengan pertanyaan.',
                ]);
            }

            $counts = collect($optionCounts)->pluck('count')->map(fn ($count) => (int) $count);

            if ($counts->contains(fn ($count) => $count < 0 || $count > $respondentCount)) {
                throw ValidationException::withMessages([
                    "distributions.{$question->id}" => 'Jumlah pilihan harus berada antara 0 dan jumlah responden.',
                ]);
            }

            if ($question->type !== 'checkbox' && $counts->sum() !== $respondentCount) {
                throw ValidationException::withMessages([
                    "distributions.{$question->id}" => "Total pilihan harus tepat {$respondentCount} responden.",
                ]);
            }

            if ($question->type === 'checkbox' && $question->is_required && $counts->sum() < $respondentCount) {
                throw ValidationException::withMessages([
                    "distributions.{$question->id}" => "Pertanyaan wajib membutuhkan minimal {$respondentCount} total pilihan.",
                ]);
            }
        }
    }

    private function makeQuestionAnswers(
        Question $question,
        int $respondentCount,
        array $optionCounts,
        array $identities,
    ): array {
        if (in_array($question->type, ['radio', 'select', 'scale'], true)) {
            $pool = [];
            foreach ($optionCounts as $item) {
                $pool = [...$pool, ...array_fill(0, (int) $item['count'], (string) $item['option'])];
            }
            shuffle($pool);

            return $pool;
        }

        if ($question->type === 'checkbox') {
            return $this->makeCheckboxAnswers($question, $respondentCount, $optionCounts);
        }

        return array_map(
            fn (array $identity) => $this->makeFreeAnswer($question, $identity),
            $identities,
        );
    }

    private function makeCheckboxAnswers(Question $question, int $respondentCount, array $optionCounts): array
    {
        $answers = array_fill(0, $respondentCount, []);
        $remaining = collect($optionCounts)->mapWithKeys(
            fn (array $item) => [(string) $item['option'] => (int) $item['count']],
        )->all();

        if ($question->is_required) {
            for ($index = 0; $index < $respondentCount; $index++) {
                arsort($remaining);
                $option = array_key_first(array_filter($remaining, fn ($count) => $count > 0));
                $answers[$index][] = $option;
                $remaining[$option]--;
            }
        }

        foreach ($remaining as $option => $count) {
            if ($count <= 0) {
                continue;
            }

            $candidates = array_values(array_filter(
                range(0, $respondentCount - 1),
                fn (int $index) => ! in_array($option, $answers[$index], true),
            ));
            shuffle($candidates);

            foreach (array_slice($candidates, 0, $count) as $index) {
                $answers[$index][] = $option;
            }
        }

        return array_map(fn (array $answer) => json_encode($answer), $answers);
    }

    private function makeFreeAnswer(Question $question, array $identity): string
    {
        $text = strtolower($question->question_text);

        if (str_contains($text, 'nama')) {
            return $identity['name'];
        }
        if (str_contains($text, 'email')) {
            return $identity['email'];
        }
        if (str_contains($text, 'telepon') || str_contains($text, 'nomor hp') || str_contains($text, 'whatsapp')) {
            return $identity['phone'];
        }
        if (str_contains($text, 'kota') || str_contains($text, 'kabupaten') || str_contains($text, 'tempat')) {
            return self::CITIES[array_rand(self::CITIES)];
        }
        if (str_contains($text, 'pekerjaan') || str_contains($text, 'profesi')) {
            return self::JOBS[array_rand(self::JOBS)];
        }
        if (str_contains($text, 'perusahaan') || str_contains($text, 'instansi')) {
            return self::COMPANIES[array_rand(self::COMPANIES)];
        }
        if ($question->type === 'number') {
            return (string) random_int(1, 100);
        }
        if ($question->type === 'date') {
            return now()->subDays(random_int(30, 1825))->format('Y-m-d');
        }
        if (in_array($question->type, ['textarea', 'long_text'], true)) {
            return self::LONG_TEXTS[array_rand(self::LONG_TEXTS)];
        }

        $shortAnswers = ['Baik', 'Sesuai', 'Sudah terpenuhi', 'Cukup membantu', 'Tidak ada'];

        return $shortAnswers[array_rand($shortAnswers)];
    }

    private function makePublicIdentities(int $questionnaireId, int $count): array
    {
        $batch = now()->format('YmdHis');
        $identities = [];

        for ($index = 1; $index <= $count; $index++) {
            $firstName = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
            $lastName = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
            $name = "{$firstName} {$lastName}";
            $emailName = strtolower("{$firstName}.{$lastName}");

            $identities[] = [
                'alumni_id' => null,
                'name' => $name,
                'email' => "{$emailName}.q{$questionnaireId}.{$batch}.{$index}@example.test",
                'phone' => '08'.random_int(11, 99).random_int(10000000, 99999999),
            ];
        }

        return $identities;
    }

    private function makeAlumniIdentities(Questionnaire $questionnaire, int $count, bool $replaceGenerated): array
    {
        $prodiIds = $questionnaire->prodis()->pluck('prodis.id');

        if ($replaceGenerated) {
            // Pool: semua alumni KECUALI yang punya respons ASLI (generated boleh ditimpa)
            $query = Alumni::query()
                ->whereDoesntHave('responses', fn ($response) => $response
                    ->where('questionnaire_id', $questionnaire->id)
                    ->where('is_generated', false));
        } else {
            // Pool: hanya alumni yang BELUM PERNAH ngisi (baik asli maupun generated)
            $query = Alumni::query()
                ->whereDoesntHave('responses', fn ($response) => $response
                    ->where('questionnaire_id', $questionnaire->id));
        }

        if ($prodiIds->isNotEmpty()) {
            $query->whereIn('prodi_id', $prodiIds);
        }

        $availableCount = (clone $query)->count();
        if ($availableCount < $count) {
            throw ValidationException::withMessages([
                'respondent_count' => "Hanya tersedia {$availableCount} alumni yang dapat digunakan.",
            ]);
        }

        return $query
            ->inRandomOrder()
            ->limit($count)
            ->get()
            ->map(fn (Alumni $alumni) => [
                'alumni_id' => $alumni->alumni_id,
                'name' => $alumni->nama,
                'email' => $alumni->email,
                'phone' => $alumni->no_hp ?? '',
            ])
            ->all();
    }

    private function randomDateTime(CarbonImmutable $from, CarbonImmutable $until): CarbonImmutable
    {
        return CarbonImmutable::createFromTimestamp(random_int($from->timestamp, $until->timestamp));
    }
}
