<?php

namespace Database\Seeders;

use App\Models\InterestCategory;
use Illuminate\Database\Seeder;

class InterestCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'kode' => 'R',
                'nama' => 'Teknik',
                'deskripsi' => 'Suka aktivitas praktis: alat, mesin, listrik, perbaikan, dan kerja lapangan.',
                'warna' => '#f59e0b',
                'urutan' => 1,
            ],
            [
                'kode' => 'I',
                'nama' => 'Teknologi & Sains',
                'deskripsi' => 'Suka menganalisis, memecahkan masalah, memprogram, dan memahami cara kerja sesuatu.',
                'warna' => '#3b82f6',
                'urutan' => 2,
            ],
            [
                'kode' => 'A',
                'nama' => 'Kreatif',
                'deskripsi' => 'Suka berkarya, mendesain, menulis, dan mengungkapkan ide orisinal.',
                'warna' => '#8b5cf6',
                'urutan' => 3,
            ],
            [
                'kode' => 'S',
                'nama' => 'Sosial',
                'deskripsi' => 'Suka membantu, mengajar, mendampingi, dan berinteraksi dengan orang lain.',
                'warna' => '#ec4899',
                'urutan' => 4,
            ],
            [
                'kode' => 'E',
                'nama' => 'Bisnis',
                'deskripsi' => 'Suka memimpin, meyakinkan orang, berjualan, dan mengambil peluang.',
                'warna' => '#ef4444',
                'urutan' => 5,
            ],
            [
                'kode' => 'C',
                'nama' => 'Administrasi & Data',
                'deskripsi' => 'Suka keteraturan, data, administrasi, dan detail yang konsisten.',
                'warna' => '#10b981',
                'urutan' => 6,
            ],
        ];

        foreach ($categories as $category) {
            InterestCategory::query()->updateOrCreate(
                ['kode' => $category['kode']],
                $category + ['is_active' => true]
            );
        }
    }
}
