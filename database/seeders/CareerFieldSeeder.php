<?php

namespace Database\Seeders;

use App\Models\CareerField;
use App\Models\InterestCategory;
use Illuminate\Database\Seeder;

class CareerFieldSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(InterestCategorySeeder::class);

        $categories = InterestCategory::query()
            ->whereIn('kode', ['R', 'I', 'A', 'S', 'E', 'C'])
            ->get()
            ->keyBy('kode');

        $fields = [
            [
                'nama' => 'Pengembang Web & Aplikasi',
                'job_zone' => 3,
                'contoh_pekerjaan' => ['Web developer', 'Mobile developer', 'QA tester'],
                'deskripsi' => 'Merancang, membangun, dan menguji aplikasi web atau mobile.',
                'relevansi' => ['I' => 3, 'C' => 1],
            ],
            [
                'nama' => 'Teknisi Jaringan & Dukungan TI',
                'job_zone' => 3,
                'contoh_pekerjaan' => ['Network administrator', 'IT support', 'Teknisi jaringan'],
                'deskripsi' => 'Mengelola jaringan, perangkat, dan dukungan teknis pengguna.',
                'relevansi' => ['R' => 3, 'I' => 2],
            ],
            [
                'nama' => 'Analis & Pengelola Data',
                'job_zone' => 3,
                'contoh_pekerjaan' => ['Staf data', 'Analis data junior', 'Admin database'],
                'deskripsi' => 'Mengumpulkan, membersihkan, dan menganalisis data untuk keputusan.',
                'relevansi' => ['I' => 3, 'C' => 2],
            ],
            [
                'nama' => 'Teknisi Listrik & Elektronika',
                'job_zone' => 2,
                'contoh_pekerjaan' => ['Teknisi listrik', 'Teknisi elektronika', 'Teknisi instrumentasi'],
                'deskripsi' => 'Memasang, merawat, dan memperbaiki sistem listrik atau elektronika.',
                'relevansi' => ['R' => 3, 'C' => 1],
            ],
            [
                'nama' => 'Teknisi Mesin & Otomotif',
                'job_zone' => 2,
                'contoh_pekerjaan' => ['Mekanik', 'Operator mesin', 'Teknisi maintenance'],
                'deskripsi' => 'Merawat dan memperbaiki mesin, kendaraan, atau peralatan industri.',
                'relevansi' => ['R' => 3, 'C' => 1],
            ],
            [
                'nama' => 'Teknisi Otomasi Industri',
                'job_zone' => 3,
                'contoh_pekerjaan' => ['Teknisi mekatronika', 'Teknisi PLC', 'Teknisi kontrol'],
                'deskripsi' => 'Menangani sistem otomasi, kontrol, dan mekatronika industri.',
                'relevansi' => ['R' => 3, 'I' => 2],
            ],
            [
                'nama' => 'Administrasi & Akuntansi',
                'job_zone' => 3,
                'contoh_pekerjaan' => ['Staf administrasi', 'Staf akuntansi', 'Staf pajak'],
                'deskripsi' => 'Mengelola administrasi, pencatatan, dan laporan keuangan.',
                'relevansi' => ['C' => 3, 'E' => 1],
            ],
            [
                'nama' => 'Penjualan & Pemasaran',
                'job_zone' => 2,
                'contoh_pekerjaan' => ['Sales', 'Digital marketing', 'Staf pemasaran'],
                'deskripsi' => 'Memasarkan produk atau jasa dan membangun hubungan pelanggan.',
                'relevansi' => ['E' => 3, 'S' => 1],
            ],
            [
                'nama' => 'Wirausaha',
                'job_zone' => 3,
                'contoh_pekerjaan' => ['Pemilik usaha kecil', 'Reseller', 'Pengelola toko'],
                'deskripsi' => 'Membangun dan mengelola usaha sendiri atau mikro.',
                'relevansi' => ['E' => 3, 'R' => 1],
            ],
            [
                'nama' => 'Instruktur & Pendampingan',
                'job_zone' => 3,
                'contoh_pekerjaan' => ['Tutor', 'Instruktur pelatihan', 'Fasilitator komunitas'],
                'deskripsi' => 'Mengajar, melatih, atau mendampingi orang lain belajar keterampilan.',
                'relevansi' => ['S' => 3, 'A' => 1],
            ],
            [
                'nama' => 'HRD & Layanan Pelanggan',
                'job_zone' => 3,
                'contoh_pekerjaan' => ['Staf HRD', 'Customer relations', 'Resepsionis'],
                'deskripsi' => 'Melayani orang, mengelola SDM, atau mendukung layanan pelanggan.',
                'relevansi' => ['S' => 3, 'E' => 2],
            ],
            [
                'nama' => 'Desain Grafis & Konten',
                'job_zone' => 3,
                'contoh_pekerjaan' => ['Desainer grafis', 'Editor video', 'Fotografer', 'Content creator'],
                'deskripsi' => 'Menciptakan visual, video, foto, atau konten kreatif.',
                'relevansi' => ['A' => 3, 'I' => 1],
            ],
            [
                'nama' => 'Desain Antarmuka (UI/UX)',
                'job_zone' => 3,
                'contoh_pekerjaan' => ['UI/UX designer', 'Desainer produk digital'],
                'deskripsi' => 'Merancang pengalaman dan antarmuka produk digital yang mudah dipakai.',
                'relevansi' => ['A' => 3, 'I' => 2],
            ],
        ];

        foreach ($fields as $field) {
            $relevansi = $field['relevansi'];
            unset($field['relevansi']);

            $model = CareerField::query()->updateOrCreate(
                ['nama' => $field['nama']],
                [
                    'deskripsi' => $field['deskripsi'],
                    'contoh_pekerjaan' => $field['contoh_pekerjaan'],
                    'job_zone' => $field['job_zone'],
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
