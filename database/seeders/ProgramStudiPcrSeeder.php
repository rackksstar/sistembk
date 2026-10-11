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

        // Diselaraskan dengan https://pmb.pcr.ac.id/prodi dan https://pcr.ac.id
        $programs = [
            [
                'nama' => 'Teknik Informatika',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Informasi',
                'deskripsi' => 'Sarjana Terapan Teknik Informatika (S.Tr.Kom.) fokus software engineering, data engineering, dan smart system.',
                'prospek_karier' => 'Software Engineer, Data Engineer, Programmer, Front-End/Back-End Developer, IT Administrator',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['I' => 3, 'C' => 1],
            ],
            [
                'nama' => 'Sistem Informasi',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Informasi',
                'deskripsi' => 'Sarjana Terapan Sistem Informasi (S.Tr.Kom.) fokus aplikasi bisnis, integrasi sistem, dan manajemen data.',
                'prospek_karier' => 'Business Analyst, System Analyst, Database Administrator, UI/UX Designer, ERP Analyst',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['C' => 3, 'I' => 2, 'E' => 1],
            ],
            [
                'nama' => 'Teknologi Rekayasa Komputer',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Informasi',
                'deskripsi' => 'Sarjana Terapan Teknologi Rekayasa Komputer (S.Tr.T.) fokus cybersecurity, computer network, dan IoT.',
                'prospek_karier' => 'Cyber Security Engineer, Network Engineer, Cloud Engineer, Embedded System Engineer, IoT Developer',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['I' => 3, 'R' => 2],
            ],
            [
                'nama' => 'Animasi',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Informasi',
                'deskripsi' => 'Sarjana Terapan Animasi (S.Tr.Anim.) fokus 3D animation, game development, dan interactive multimedia (AR/VR/MR).',
                'prospek_karier' => 'Animator, Digital Modeler, Game Developer, Interactive Multimedia Developer, Wirausaha Kreatif',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['A' => 3, 'I' => 1],
            ],
            [
                'nama' => 'Teknik Elektronika',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Industri',
                'deskripsi' => 'Sarjana Terapan Teknik Elektronika (S.Tr.T.) mencakup RF, optical fiber, network, embedded system, dan IoT.',
                'prospek_karier' => 'Industri telekomunikasi, migas, listrik, instrumentasi, system administrator, wirausaha',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['R' => 3, 'I' => 2],
            ],
            [
                'nama' => 'Teknologi Rekayasa Sistem Elektronika',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Industri',
                'deskripsi' => 'Sarjana Terapan TRSE (S.Tr.T.) fokus engineering design/analysis, IoT electronic system, embedded, dan sensor.',
                'prospek_karier' => 'Industrial manufacture/services, IoT Developer, power plant, metrology, entrepreneur',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['R' => 3, 'I' => 2],
            ],
            [
                'nama' => 'Teknologi Rekayasa Jaringan Telekomunikasi',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Industri',
                'deskripsi' => 'Sarjana Terapan TRJT (S.Tr.T.) fokus computer network, IoT, RF, optical fiber, dan embedded system.',
                'prospek_karier' => 'Network Engineer/Administrator, IoT Developer, RF Engineer, Optical Network Supervisor',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['R' => 3, 'I' => 2],
            ],
            [
                'nama' => 'Teknologi Rekayasa Mekatronika',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Industri',
                'deskripsi' => 'Sarjana Terapan Mekatronika (S.Tr.T.) fokus mechatronics, automation, design, dan manufacture engineering.',
                'prospek_karier' => 'Mechatronics Engineer, Automation Engineer, Manufacturing Engineer, Project Engineer',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['R' => 3, 'I' => 2],
            ],
            [
                'nama' => 'Teknik Listrik',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Industri',
                'deskripsi' => 'Sarjana Terapan Teknik Listrik (S.Tr.T.) fokus instalasi, proteksi, otomasi tenaga listrik, SCADA, dan renewable energy.',
                'prospek_karier' => 'Electrical designer/installer, project engineer, supervisor, instructor, technopreneur',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['R' => 3, 'C' => 1],
            ],
            [
                'nama' => 'Teknik Mesin',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Industri',
                'deskripsi' => 'Sarjana Terapan Teknik Mesin (S.Tr.T.) fokus desain produk, metrologi, welding, fabrication, piping/fluid/energy.',
                'prospek_karier' => 'Design Engineer, Field Engineer, Project Engineer, Welding/Fabrication specialist',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['R' => 3],
            ],
            [
                'nama' => 'Kecerdasan Buatan dan Robotika',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Teknologi Industri',
                'deskripsi' => 'Sarjana Terapan KBR (S.Tr.T.) fokus robotika, AI/machine learning, IoT, dan otomasi industri.',
                'prospek_karier' => 'Robotics Engineer, AI Engineer, IoT Engineer, Automation Engineer',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['I' => 3, 'R' => 2],
            ],
            [
                'nama' => 'Akuntansi Perpajakan',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Bisnis dan Komunikasi',
                'deskripsi' => 'Sarjana Terapan Akuntansi Perpajakan (S.Tr.Ak.) fokus akuntansi keuangan, perpajakan, perkebunan, dan perminyakan.',
                'prospek_karier' => 'Company Accountant, Auditor, Tax Officer/Consultant, Accounting Consultant',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['C' => 3, 'E' => 1],
            ],
            [
                'nama' => 'Hubungan Masyarakat dan Komunikasi Digital',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Bisnis dan Komunikasi',
                'deskripsi' => 'Sarjana Terapan HMKD (S.Tr.I.Kom.) fokus public relations, digital media strategist, dan virtual event.',
                'prospek_karier' => 'PR Consultant, Social Media Specialist, Content Creator, Marketing Communication Specialist',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['A' => 2, 'E' => 2, 'S' => 2],
            ],
            [
                'nama' => 'Bisnis Digital',
                'jenjang_pendidikan' => 'D4',
                'jurusan' => 'Bisnis dan Komunikasi',
                'deskripsi' => 'Sarjana Terapan Bisnis Digital (S.Tr.Bns.) fokus technopreneur, digital entrepreneur, dan analisis data bisnis digital.',
                'prospek_karier' => 'Digital Entrepreneur, Data Analyst, Digital Marketing Specialist, Product Manager, Business Development',
                'website_url' => 'https://pmb.pcr.ac.id/prodi',
                'relevansi' => ['E' => 3, 'C' => 2],
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
                    'deskripsi' => $program['deskripsi'],
                    'prospek_karier' => $program['prospek_karier'],
                    'website_url' => $program['website_url'],
                    'is_verified' => true,
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
