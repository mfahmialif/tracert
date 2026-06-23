<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Year;

class YearSeeder extends Seeder
{
    public function run(): void
    {
        $years = [
            ['code' => '20161', 'name' => '2016/2017', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20162', 'name' => '2016/2017', 'smt' => 'Genap', 'is_active' => false],
            ['code' => '20171', 'name' => '2017/2018', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20172', 'name' => '2017/2018', 'smt' => 'Genap', 'is_active' => false],
            ['code' => '20181', 'name' => '2018/2019', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20182', 'name' => '2018/2019', 'smt' => 'Genap', 'is_active' => false],
            ['code' => '20191', 'name' => '2019/2020', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20192', 'name' => '2019/2020', 'smt' => 'Genap', 'is_active' => false],
            ['code' => '20201', 'name' => '2020/2021', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20202', 'name' => '2020/2021', 'smt' => 'Genap', 'is_active' => false],
            ['code' => '20211', 'name' => '2021/2022', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20212', 'name' => '2021/2022', 'smt' => 'Genap', 'is_active' => false],
            ['code' => '20221', 'name' => '2022/2023', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20222', 'name' => '2022/2023', 'smt' => 'Genap', 'is_active' => false],
            ['code' => '20231', 'name' => '2023/2024', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20232', 'name' => '2023/2024', 'smt' => 'Genap', 'is_active' => false],
            ['code' => '20241', 'name' => '2024/2025', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20242', 'name' => '2024/2025', 'smt' => 'Genap', 'is_active' => false],
            ['code' => '20251', 'name' => '2025/2026', 'smt' => 'Ganjil', 'is_active' => false],
            ['code' => '20252', 'name' => '2025/2026', 'smt' => 'Genap', 'is_active' => true],
        ];

        foreach ($years as $year) {
            Year::updateOrCreate(
                ['code' => $year['code']],
                $year,
            );
        }
    }
}
