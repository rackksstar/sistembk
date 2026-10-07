<?php

namespace Database\Seeders;

use App\Models\InterestCategory;
use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;

class ProgramStudiPcrSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(InterestCategorySeeder::class);

        $categories = InterestCategory::query()
            ->whereIn('kode', ['R', 'I', 'A', 'S', 'E', 'C'])
            ->get()
            ->keyBy('kode');

        $programs = [
            [
                'nama' => 'Teknik Informatika',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknik Informatika',
                'relevansi' => ['I' => 3, 'C' => 1],
            ],
            [
                'nama' => 'Sistem Informasi',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Sistem Informasi',
                'relevansi' => ['C' => 3, 'I' => 2, 'E' => 1],
            ],
            [
                'nama' => 'Teknologi Rekayasa Komputer',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Rekayasa Komputer',
                'relevansi' => ['I' => 3, 'R' => 2],
            ],
            [
                'nama' => 'Teknologi Rekayasa Sistem Elektronika',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Rekayasa Sistem Elektronika',
                'relevansi' => ['R' => 3, 'I' => 2],
            ],
            [
                'nama' => 'Teknologi Rekayasa Jaringan Telekomunikasi',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Rekayasa Jaringan Telekomunikasi',
                'relevansi' => ['R' => 3, 'I' => 2],
            ],
            [
                'nama' => 'Teknologi Rekayasa Mekatronika',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Rekayasa Mekatronika',
                'relevansi' => ['R' => 3, 'I' => 2],
            ],
            [
                'nama' => 'Teknik Listrik',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknik Listrik',
                'relevansi' => ['R' => 3, 'C' => 1],
            ],
            [
                'nama' => 'Teknik Mesin',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknik Mesin',
                'relevansi' => ['R' => 3],
            ],
            [
                'nama' => 'Akuntansi Perpajakan',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Akuntansi Perpajakan',
                'relevansi' => ['C' => 3, 'E' => 1],
            ],
        ];

        foreach ($programs as $program) {
            $relevansi = $program['relevansi'];
            unset($program['relevansi']);

            $model = ProgramStudi::query()->updateOrCreate(
                [
                    'institusi' => 'Politeknik Caltex Riau',
                    'nama' => $program['nama'],
                    'jenjang_pendidikan' => $program['jenjang_pendidikan'],
                ],
                [
                    'jurusan' => $program['jurusan'],
                    'deskripsi' => null,
                    'prospek_karier' => null,
                    'website_url' => 'https://pmb.pcr.ac.id',
                    'is_verified' => false,
                    'is_active' => true,
                ]
            );

            $sync = [];
            foreach ($relevansi as $kode => $nilai) {
                $category = $categories->get($kode);
                if ($category) {
                    $sync[$category->id] = ['relevansi' => $nilai];
                }
            }

            $model->interestCategories()->sync($sync);
        }
    }
}
