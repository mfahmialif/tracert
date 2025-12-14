<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionnaireType;

class QuestionnaireTypeSeeder extends Seeder
{
    public function run(): void
    {
        QuestionnaireType::insert([
            ['name' => 'Tracer Study Umum', 'description' => 'Kuesioner utama tracer study untuk semua alumni', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kepuasan Lulusan', 'description' => 'Mengukur kepuasan lulusan terhadap layanan kampus', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Relevansi Kurikulum', 'description' => 'Mengevaluasi relevansi kurikulum dengan dunia kerja', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dunia Kerja', 'description' => 'Informasi pekerjaan dan karir alumni', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wirausaha', 'description' => 'Untuk alumni yang berwirausaha', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Studi Lanjut', 'description' => 'Untuk alumni yang melanjutkan pendidikan', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
