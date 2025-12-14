<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Questionnaire;
use App\Models\Question;

class QuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        // Tracer Study Umum 2024
        $year = \App\Models\Year::first() ?? \App\Models\Year::create(['code' => '2024', 'name' => '2024', 'is_active' => true]);

        // Tracer Study Umum 2024
        $questionnaire = Questionnaire::create([
            'year_id' => $year->id,
            'title' => 'Tracer Study 2024',
            'description' => 'Kuesioner tracer study untuk lulusan tahun 2024',
            'is_mandatory' => true,
            'is_active' => true,
            'start_date' => '2024-01-01',
            'end_date' => '2025-12-31',
        ]);

        // Attach to random prodis
        $prodis = \App\Models\Prodi::inRandomOrder()->take(3)->pluck('id');
        $questionnaire->prodis()->attach($prodis);

        // Section 1: Data Pribadi
        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Apakah Anda sudah bekerja?',
            'type' => 'radio',
            'options' => ['Ya', 'Tidak'],
            'is_required' => true,
            'order' => 1,
            'section' => 1,
        ]);

        $q2 = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Berapa lama waktu tunggu Anda mendapatkan pekerjaan pertama setelah lulus?',
            'type' => 'radio',
            'options' => ['< 3 bulan', '3-6 bulan', '6-12 bulan', '> 12 bulan'],
            'is_required' => true,
            'order' => 2,
            'section' => 1,
            'depends_on' => 1,
            'depends_value' => 'Ya',
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Nama perusahaan/instansi tempat Anda bekerja saat ini',
            'type' => 'text',
            'options' => null,
            'is_required' => true,
            'order' => 3,
            'section' => 1,
            'depends_on' => 1,
            'depends_value' => 'Ya',
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Jenis pekerjaan Anda saat ini',
            'type' => 'select',
            'options' => ['PNS/ASN', 'Pegawai Swasta', 'Wirausaha', 'Freelancer', 'BUMN', 'TNI/Polri', 'Lainnya'],
            'is_required' => true,
            'order' => 4,
            'section' => 1,
            'depends_on' => 1,
            'depends_value' => 'Ya',
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Apakah Anda sedang mencari pekerjaan?',
            'type' => 'radio',
            'options' => ['Ya', 'Tidak'],
            'is_required' => true,
            'order' => 5,
            'section' => 1,
            'depends_on' => 1,
            'depends_value' => 'Tidak',
        ]);

        // Section 2: Kepuasan
        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Seberapa puas Anda dengan kualitas pembelajaran di kampus?',
            'type' => 'scale',
            'options' => ['1' => 'Sangat Tidak Puas', '2' => 'Tidak Puas', '3' => 'Cukup', '4' => 'Puas', '5' => 'Sangat Puas'],
            'is_required' => true,
            'order' => 1,
            'section' => 2,
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Seberapa relevan kurikulum dengan pekerjaan Anda?',
            'type' => 'scale',
            'options' => ['1' => 'Sangat Tidak Relevan', '2' => 'Tidak Relevan', '3' => 'Cukup', '4' => 'Relevan', '5' => 'Sangat Relevan'],
            'is_required' => true,
            'order' => 2,
            'section' => 2,
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Kompetensi apa yang paling bermanfaat untuk pekerjaan Anda? (Pilih maksimal 3)',
            'type' => 'checkbox',
            'options' => ['Pengetahuan Teknis', 'Keterampilan Komunikasi', 'Kerja Tim', 'Problem Solving', 'Kepemimpinan', 'Bahasa Asing', 'Teknologi Informasi'],
            'is_required' => true,
            'order' => 3,
            'section' => 2,
        ]);

        // Section 3: Saran
        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question_text' => 'Saran dan masukan untuk peningkatan kualitas kampus',
            'type' => 'textarea',
            'options' => null,
            'is_required' => false,
            'order' => 1,
            'section' => 3,
        ]);

        // Kepuasan Lulusan 2024
        // Kepuasan Lulusan 2024
        $questionnaire2 = Questionnaire::create([
            'year_id' => $year->id,
            'title' => 'Kepuasan Lulusan 2024',
            'description' => 'Survei kepuasan lulusan terhadap layanan kampus',
            'is_mandatory' => false,
            'is_active' => true,
            'start_date' => '2024-01-01',
            'end_date' => '2025-12-31',
        ]);
        $questionnaire2->prodis()->sync(\App\Models\Prodi::pluck('id')); // All prodis

        Question::create([
            'questionnaire_id' => $questionnaire2->id,
            'question_text' => 'Kepuasan terhadap layanan akademik',
            'type' => 'scale',
            'options' => ['1' => 'Sangat Tidak Puas', '2' => 'Tidak Puas', '3' => 'Cukup', '4' => 'Puas', '5' => 'Sangat Puas'],
            'is_required' => true,
            'order' => 1,
            'section' => 1,
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire2->id,
            'question_text' => 'Kepuasan terhadap fasilitas kampus',
            'type' => 'scale',
            'options' => ['1' => 'Sangat Tidak Puas', '2' => 'Tidak Puas', '3' => 'Cukup', '4' => 'Puas', '5' => 'Sangat Puas'],
            'is_required' => true,
            'order' => 2,
            'section' => 1,
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire2->id,
            'question_text' => 'Kepuasan terhadap dosen',
            'type' => 'scale',
            'options' => ['1' => 'Sangat Tidak Puas', '2' => 'Tidak Puas', '3' => 'Cukup', '4' => 'Puas', '5' => 'Sangat Puas'],
            'is_required' => true,
            'order' => 3,
            'section' => 1,
        ]);

        // Generate 100 Random Questionnaires
        $faker = \Faker\Factory::create('id_ID');
        $years = \App\Models\Year::all();
        $prodiIds = \App\Models\Prodi::pluck('id')->toArray();

        for ($i = 0; $i < 100; $i++) {
            $q = Questionnaire::create([
                'year_id' => $years->random()->id,
                'title' => $faker->sentence(4),
                'description' => $faker->paragraph(),
                'is_mandatory' => $faker->boolean(30), // 30% mandatory
                'is_active' => true,
                'start_date' => $faker->dateTimeBetween('-1 year', 'now'),
                'end_date' => $faker->dateTimeBetween('now', '+1 year'),
            ]);

            // Attach to 1-3 random prodis
            $randomProdis = $faker->randomElements($prodiIds, $faker->numberBetween(1, 3));
            $q->prodis()->attach($randomProdis);

            // Add 3-5 Questions
            $numQuestions = $faker->numberBetween(3, 5);
            for ($j = 1; $j <= $numQuestions; $j++) {
                $type = $faker->randomElement(['text', 'radio', 'checkbox', 'scale', 'textarea']);
                $options = null;

                if (in_array($type, ['radio', 'checkbox', 'select'])) {
                    $options = $faker->words($faker->numberBetween(3, 5));
                } elseif ($type === 'scale') {
                    $options = [
                        '1' => 'Sangat Buruk',
                        '2' => 'Buruk',
                        '3' => 'Cukup',
                        '4' => 'Baik',
                        '5' => 'Sangat Baik'
                    ];
                }

                Question::create([
                    'questionnaire_id' => $q->id,
                    'question_text' => $faker->sentence() . '?',
                    'type' => $type,
                    'options' => $options,
                    'is_required' => $faker->boolean(80),
                    'order' => $j,
                    'section' => 1,
                ]);
            }
        }
    }
}
