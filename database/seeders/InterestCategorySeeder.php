<?php

namespace Database\Seeders;

use App\Models\InstrumentQuestion;
use App\Models\InterestCategory;
use Illuminate\Database\Seeder;

class InterestCategorySeeder extends Seeder
{
    public function run(): void
    {
        $descriptions = config('riasec_results.descriptions', []);
        $warna = [
            'R' => '#f59e0b',
            'I' => '#3b82f6',
            'A' => '#8b5cf6',
            'S' => '#10b981',
            'E' => '#ef4444',
            'C' => '#06b6d4',
        ];
        $urutan = 1;

        foreach (InstrumentQuestion::RIASEC_CODES as $kode => $nama) {
            InterestCategory::query()->updateOrCreate(
                ['kode' => $kode],
                [
                    'nama' => $nama,
                    'deskripsi' => $descriptions[$kode] ?? $nama,
                    'warna' => $warna[$kode] ?? '#64748b',
                    'urutan' => $urutan++,
                    'is_active' => true,
                ]
            );
        }
    }
}
