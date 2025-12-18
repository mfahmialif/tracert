<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Year;

class YearSeeder extends Seeder
{
    public function run(): void
    {
        $years = [
            ['code' => '20231', 'name' => '2023/2024 Ganjil', 'smt' => 'Ganjil', 'is_active' => true],
            ['code' => '20232', 'name' => '2023/2024 Genap', 'smt' => 'Genap', 'is_active' => true],
            ['code' => '20221', 'name' => '2022/2023 Ganjil', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20222', 'name' => '2022/2023 Genap', 'smt' => 'Genap', 'is_active' => false],
            ['code' => '20211', 'name' => '2021/2022 Ganjil', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20212', 'name' => '2021/2022 Genap', 'smt' => 'Genap', 'is_active' => false],
        ];

        foreach ($years as $year) {
            Year::create($year);
        }
    }
}
