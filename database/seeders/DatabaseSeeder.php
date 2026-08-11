<?php

namespace Database\Seeders;

use App\Models\CareerInfo;
use App\Models\ConsultationRequest;
use App\Models\GuruBk;
use App\Models\GuidanceClass;
use App\Models\InstrumentAnswer;
use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use App\Models\Kelas;
use App\Models\MonthlyJournal;
use App\Models\Rpl;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Sekolah;
use App\Models\ServiceFeedback;
use App\Models\SociometryResponse;
use App\Models\Student;
use App\Models\SiswaSmk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->updateOrCreate(
            ['npsn' => '20260001'],
            [
                'name' => 'SMA Negeri 1 Contoh',
                'address' => 'Jl. Pendidikan No. 10',
            ]
        );

        $classXiiIpa = SchoolClass::query()->updateOrCreate(
            ['school_id' => $school->id, 'name' => 'XII IPA 1'],
            ['level' => 'XII']
        );

        $classXiIps = SchoolClass::query()->updateOrCreate(
            ['school_id' => $school->id, 'name' => 'XI IPS 2'],
            ['level' => 'XI']
        );

        $sekolah = Sekolah::query()->updateOrCreate(
            ['nama' => 'SMA Negeri 1 Contoh'],
            [
                'npsn' => '20260001',
                'alamat' => 'Jl. Pendidikan No. 10',
                'is_mou' => true,
                'paket_aktif' => 'Basic',
                'tanggal_aktivasi' => now()->toDateString(),
                'is_active' => true,
            ]
        );

        $kelasXii = Kelas::query()->updateOrCreate(
            ['sekolah_id' => $sekolah->id, 'nama' => 'XII IPA 1'],
            [
                'jenjang' => 'SMA',
                'tingkatan' => 'XII',
            ]
        );

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@bk.test'],
            [
                'name' => 'Admin BK',
                'password' => Hash::make('password'),
                'school' => $school->name,
                'school_id' => $school->id,
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin.demo@bk.test'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('password'),
                'school' => $school->name,
                'school_id' => $school->id,
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]
        );

        $guru = User::query()->updateOrCreate(
            ['username' => '081234567890'],
            [
                'name' => 'Ibu Rina Guru BK',
                'email' => null,
                'password' => Hash::make('password'),
                'school' => $school->name,
                'school_id' => $school->id,
                'role' => User::ROLE_GURU,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => null,
            ]
        );

        GuruBk::query()->updateOrCreate(
            ['user_id' => $guru->id],
            [
                'sekolah_id' => $sekolah->id,
                'no_hp' => '081234567890',
                'nip' => '1987654321001',
                'jabatan' => 'Guru BK',
                'bidang_studi' => 'Bimbingan Konseling',
            ]
        );

        $siswa = User::query()->updateOrCreate(
            ['email' => 'siswa@bk.test'],
            [
                'name' => 'Andi Siswa',
                'password' => Hash::make('password'),
                'school' => $school->name,
                'school_id' => $school->id,
                'class_id' => $classXiiIpa->id,
                'role' => User::ROLE_SISWA,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]
        );

        $pendingGuru = User::query()->updateOrCreate(
            ['username' => '081234567891'],
            [
                'name' => 'Pak Dimas Guru Pending',
                'email' => null,
                'password' => Hash::make('password'),
                'school' => $school->name,
                'school_id' => $school->id,
                'role' => User::ROLE_GURU,
                'status' => User::STATUS_PENDING,
                'email_verified_at' => null,
            ]
        );

        GuruBk::query()->updateOrCreate(
            ['user_id' => $pendingGuru->id],
            [
                'sekolah_id' => $sekolah->id,
                'no_hp' => '081234567891',
                'nip' => '1987654321002',
                'jabatan' => 'Guru BK',
                'bidang_studi' => 'Bimbingan Konseling',
            ]
        );

        ConsultationRequest::query()->updateOrCreate(
            ['student_id' => $siswa->id, 'subject' => 'Persiapan ujian akhir'],
            [
                'counselor_id' => $guru->id,
                'case_category' => ConsultationRequest::CASE_BELAJAR,
                'preferred_time' => 'Senin pagi',
                'details' => 'Saya ingin berdiskusi tentang manajemen waktu belajar sebelum ujian.',
                'status' => ConsultationRequest::STATUS_APPROVED,
                'consultation_date' => now()->addDays(3)->toDateString(),
                'consultation_time' => '09:00',
                'scheduled_at' => now()->addDays(3)->setTime(9, 0),
                'notes' => 'Sesi awal sudah dijadwalkan.',
                'result' => 'Siswa memahami hambatan utama berupa distraksi gawai dan belum memiliki jadwal belajar harian.',
                'evaluation' => 'Siswa aktif menyusun prioritas kegiatan dan bersedia mencoba jadwal belajar selama satu minggu.',
                'follow_up' => 'Pantau jurnal belajar siswa pada sesi berikutnya.',
            ]
        );

        ConsultationRequest::query()->updateOrCreate(
            ['student_id' => $siswa->id, 'subject' => 'Latihan komunikasi dengan teman sebaya'],
            [
                'counselor_id' => $guru->id,
                'case_category' => ConsultationRequest::CASE_SOSIAL,
                'preferred_time' => 'Kamis siang',
                'details' => 'Siswa ingin lebih percaya diri saat kerja kelompok.',
                'status' => ConsultationRequest::STATUS_SELESAI,
                'consultation_date' => now()->subDays(6)->toDateString(),
                'consultation_time' => '10:00',
                'scheduled_at' => now()->subDays(6)->setTime(10, 0),
                'notes' => 'Sesi selesai.',
                'result' => 'Siswa dapat menyebutkan contoh kalimat asertif untuk menyampaikan pendapat.',
                'evaluation' => 'Siswa perlu latihan bertahap dalam kelompok kecil.',
                'follow_up' => 'Libatkan siswa dalam bimbingan kelompok komunikasi asertif.',
            ]
        );

        ConsultationRequest::query()->updateOrCreate(
            ['student_id' => $siswa->id, 'subject' => 'Konsultasi pemilihan jurusan'],
            [
                'counselor_id' => null,
                'case_category' => ConsultationRequest::CASE_KARIER,
                'preferred_time' => 'Rabu siang',
                'details' => 'Butuh arahan untuk memilih jurusan yang sesuai minat.',
                'status' => ConsultationRequest::STATUS_PENDING,
                'scheduled_at' => null,
                'notes' => null,
            ]
        );

        $studentProfile = Student::query()->updateOrCreate(
            ['nisn' => '0061234567'],
            [
                'user_id' => $siswa->id,
                'kelas_id' => $kelasXii->id,
                'name' => $siswa->name,
                'birth_date' => '2008-05-14',
                'school' => $school->name,
                'jenis_kelamin' => 'L',
                'status_biodata' => 'lengkap',
            ]
        );

        SiswaSmk::query()->updateOrCreate(
            ['student_id' => $studentProfile->id],
            [
                'user_id' => $siswa->id,
                'name' => $studentProfile->name,
                'nisn' => $studentProfile->nisn,
                'sekolah' => 'SMK Negeri 1 Contoh',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'kelas' => 'XII RPL 1',
                'tahun_lulus' => now()->year,
                'nomor_hp' => '081234567899',
                'email' => $siswa->email,
                'alamat' => 'Jl. Pendidikan No. 10',
                'keahlian' => ['HTML', 'CSS', 'Laravel', 'UI dasar'],
                'pengalaman' => 'Pernah membuat aplikasi pencatatan sederhana untuk tugas akhir sekolah.',
                'status_kerja' => 'mencari_kerja',
                'siap_dihubungi' => true,
            ]
        );

        $guidanceClass = GuidanceClass::query()->updateOrCreate(
            ['name' => 'Kelas Bimbingan Karier XII'],
            [
                'code' => 'BK-KARIER',
                'description' => 'Kelompok bimbingan untuk persiapan studi lanjut dan pilihan karier.',
            ]
        );

        $guidanceClass->students()->syncWithoutDetaching([$studentProfile->id]);

        $studentUsers = collect([$siswa]);

        foreach ([
            ['name' => 'Alya Putri', 'email' => 'alya@bk.test', 'nisn' => '0061234568', 'birth_date' => '2008-06-20', 'jenis_kelamin' => 'P'],
            ['name' => 'Bima Pratama', 'email' => 'bima@bk.test', 'nisn' => '0061234569', 'birth_date' => '2008-08-02', 'jenis_kelamin' => 'L'],
            ['name' => 'Citra Lestari', 'email' => 'citra@bk.test', 'nisn' => '0061234570', 'birth_date' => '2008-09-11', 'jenis_kelamin' => 'P'],
            ['name' => 'Dimas Arya', 'email' => 'dimas@bk.test', 'nisn' => '0061234571', 'birth_date' => '2008-10-21', 'jenis_kelamin' => 'L'],
        ] as $index => $item) {
            $schoolClass = $index < 2 ? $classXiiIpa : $classXiIps;

            $studentUser = User::query()->updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => Hash::make('password'),
                    'school' => $school->name,
                    'school_id' => $school->id,
                    'class_id' => $schoolClass->id,
                    'role' => User::ROLE_SISWA,
                    'status' => User::STATUS_APPROVED,
                    'email_verified_at' => now(),
                ]
            );

            $profile = Student::query()->updateOrCreate(
                ['nisn' => $item['nisn']],
                [
                    'user_id' => $studentUser->id,
                    'kelas_id' => $kelasXii->id,
                    'name' => $studentUser->name,
                    'birth_date' => $item['birth_date'],
                    'school' => $school->name,
                    'jenis_kelamin' => $item['jenis_kelamin'],
                    'status_biodata' => 'lengkap',
                ]
            );

            $guidanceClass->students()->syncWithoutDetaching([$profile->id]);
            $studentUsers->push($studentUser);
        }

        $instrumentQuestions = [
            InstrumentQuestion::CATEGORY_MINAT_BAKAT => [
                'Saya bersemangat saat mengerjakan aktivitas yang membutuhkan ide baru.',
                'Saya mudah menikmati pelajaran atau kegiatan yang menantang kemampuan berpikir.',
                'Saya memiliki aktivitas favorit yang ingin saya dalami sebagai rencana masa depan.',
            ],
            InstrumentQuestion::CATEGORY_GAYA_BELAJAR => [
                'Saya lebih mudah memahami materi ketika melihat gambar, diagram, atau warna.',
                'Saya lebih cepat mengingat materi setelah mendengar penjelasan atau diskusi.',
                'Saya senang belajar dengan praktik langsung atau membuat contoh nyata.',
            ],
            InstrumentQuestion::CATEGORY_KEPRIBADIAN => [
                'Saya mampu menenangkan diri ketika menghadapi situasi yang menekan.',
                'Saya nyaman bekerja sama dengan teman yang berbeda pendapat.',
                'Saya berani menyampaikan kebutuhan saya dengan cara yang sopan.',
            ],
            InstrumentQuestion::CATEGORY_SOSIOMETRI => [
                'Saya mudah memilih teman untuk bekerja sama dalam kelompok belajar.',
                'Saya merasa diterima dalam pergaulan kelas.',
                'Saya memiliki teman yang dapat dipercaya saat mengalami kesulitan.',
            ],
            InstrumentQuestion::CATEGORY_ANGKET_MASALAH => [
                'Saya sering merasa sulit berkonsentrasi saat belajar.',
                'Saya merasa cemas ketika menghadapi tugas atau ujian.',
                'Saya mengalami kesulitan berkomunikasi dengan teman di sekolah.',
            ],
        ];

        $defaultOptions = [
            ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
            ['label' => 'Tidak Sesuai', 'score' => 2],
            ['label' => 'Sesuai', 'score' => 3],
            ['label' => 'Sangat Sesuai', 'score' => 4],
        ];

        foreach ($instrumentQuestions as $category => $questions) {
            foreach ($questions as $questionText) {
                InstrumentQuestion::query()->updateOrCreate(
                    ['category' => $category, 'question' => $questionText],
                    [
                        'options' => $defaultOptions,
                        'is_active' => true,
                        'created_by' => $guru->id,
                    ]
                );
            }
        }

        $sampleQuestions = InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->get();

        if ($sampleQuestions->isNotEmpty()) {
            DB::transaction(function () use ($sampleQuestions, $siswa) {
                $submission = InstrumentSubmission::query()->updateOrCreate(
                    ['student_id' => $siswa->id, 'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT],
                    [
                        'total_score' => 10,
                        'result_label' => 'Sangat Menonjol',
                        'result_description' => 'Potensi atau kecenderungan siswa terlihat kuat pada instrumen ini.',
                        'submitted_at' => now(),
                    ]
                );

                foreach ($sampleQuestions as $question) {
                    InstrumentAnswer::query()->updateOrCreate(
                        [
                            'instrument_submission_id' => $submission->id,
                            'instrument_question_id' => $question->id,
                        ],
                        [
                            'answer_label' => 'Sesuai',
                            'score' => 3,
                        ]
                    );
                }
            });
        }

        $personalityQuestions = InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_KEPRIBADIAN)
            ->get();

        if ($personalityQuestions->isNotEmpty()) {
            foreach ($studentUsers->take(3) as $studentUser) {
                $submission = InstrumentSubmission::query()->updateOrCreate(
                    ['student_id' => $studentUser->id, 'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN],
                    [
                        'total_score' => 9,
                        'result_label' => 'Cukup Berkembang',
                        'result_description' => 'Kecenderungan personal siswa sudah terlihat dan dapat diperkuat melalui bimbingan.',
                        'submitted_at' => now()->subDays(rand(1, 8)),
                    ]
                );

                foreach ($personalityQuestions as $question) {
                    InstrumentAnswer::query()->updateOrCreate(
                        [
                            'instrument_submission_id' => $submission->id,
                            'instrument_question_id' => $question->id,
                        ],
                        [
                            'answer_label' => 'Sesuai',
                            'score' => 3,
                        ]
                    );
                }
            }
        }

        SociometryResponse::query()->updateOrCreate(
            ['student_id' => $siswa->id, 'relation_type' => SociometryResponse::TYPE_CLOSE_FRIEND],
            [
                'chosen_student_id' => $studentUsers[1]->id,
                'reason' => 'Sering berdiskusi dan saling membantu saat belajar.',
                'submitted_at' => now(),
            ]
        );

        SociometryResponse::query()->updateOrCreate(
            ['student_id' => $siswa->id, 'relation_type' => SociometryResponse::TYPE_STUDY_FRIEND],
            [
                'chosen_student_id' => $studentUsers[2]->id,
                'reason' => 'Cocok untuk kerja kelompok.',
                'submitted_at' => now(),
            ]
        );

        SociometryResponse::query()->updateOrCreate(
            ['student_id' => $studentUsers[1]->id, 'relation_type' => SociometryResponse::TYPE_CLOSE_FRIEND],
            [
                'chosen_student_id' => $siswa->id,
                'reason' => 'Mudah diajak komunikasi.',
                'submitted_at' => now(),
            ]
        );

        Rpl::query()->updateOrCreate(
            ['teacher_id' => $guru->id, 'title' => 'RPL Individu Manajemen Waktu Belajar'],
            [
                'type' => Rpl::TYPE_INDIVIDU,
                'service_date' => now()->addWeek()->toDateString(),
                'target' => 'Andi Siswa',
                'tujuan' => 'Siswa mampu mengenali hambatan manajemen waktu dan menyusun jadwal belajar realistis.',
                'materi' => 'Prioritas kegiatan, teknik membuat jadwal, dan evaluasi kebiasaan belajar.',
                'metode' => 'Konseling individu, refleksi terarah, dan penyusunan rencana aksi.',
                'evaluasi' => 'Siswa menunjukkan jadwal belajar mingguan dan merefleksikan pelaksanaannya pada pertemuan berikutnya.',
            ]
        );

        MonthlyJournal::query()->updateOrCreate(
            ['teacher_id' => $guru->id, 'month' => now()->month, 'year' => now()->year],
            [
                'title' => 'Jurnal Layanan BK Bulan Ini',
                'individual_services' => 8,
                'group_services' => 3,
                'classical_services' => 4,
                'summary' => 'Layanan bulan ini berfokus pada manajemen belajar, komunikasi sosial, dan kesiapan karier siswa.',
                'evaluation' => 'Sebagian besar siswa mampu mengikuti layanan dengan aktif, namun beberapa siswa masih perlu pendampingan individual.',
                'follow_up' => 'Menjadwalkan sesi lanjutan untuk siswa prioritas dan memperkuat layanan kelompok komunikasi asertif.',
            ]
        );

        ServiceFeedback::query()->updateOrCreate(
            ['student_id' => $siswa->id, 'service_type' => 'Konseling Individu'],
            [
                'consultation_request_id' => ConsultationRequest::query()
                    ->where('student_id', $siswa->id)
                    ->where('status', ConsultationRequest::STATUS_SELESAI)
                    ->value('id'),
                'rating' => 5,
                'message' => 'Saya merasa lebih terbantu menyusun langkah belajar dan lebih tenang setelah konseling.',
                'suggestion' => 'Sesi lanjutan bisa dibuat lebih sering saat mendekati ujian.',
            ]
        );

        Rpl::query()->updateOrCreate(
            ['teacher_id' => $guru->id, 'title' => 'RPL Kelompok Komunikasi Asertif'],
            [
                'type' => Rpl::TYPE_KELOMPOK,
                'service_date' => now()->addWeeks(2)->toDateString(),
                'target' => 'Kelompok siswa kelas bimbingan karier',
                'tujuan' => 'Siswa mampu menyampaikan pendapat secara jelas, sopan, dan menghargai orang lain.',
                'materi' => 'Konsep komunikasi asertif, contoh kalimat asertif, dan latihan respons sosial.',
                'metode' => 'Bimbingan kelompok, diskusi, permainan peran, dan umpan balik.',
                'evaluasi' => 'Guru BK mengamati partisipasi siswa dan meminta refleksi singkat setelah kegiatan.',
            ]
        );

        User::factory()
            ->count(6)
            ->sequence(
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED],
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED],
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED],
            )
            ->create();

        CareerInfo::query()->updateOrCreate(
            ['title' => 'Software Engineer'],
            [
                'description' => 'Merancang, membangun, dan memelihara aplikasi digital. Cocok untuk siswa yang senang logika, problem solving, dan teknologi.',
                'category' => 'Teknologi',
            ]
        );

        CareerInfo::query()->updateOrCreate(
            ['title' => 'Konselor Pendidikan'],
            [
                'description' => 'Membantu siswa memahami potensi diri, pilihan studi, serta strategi belajar yang lebih sehat dan terarah.',
                'category' => 'Pendidikan',
            ]
        );

        CareerInfo::query()->updateOrCreate(
            ['title' => 'Desainer UI/UX'],
            [
                'description' => 'Menciptakan pengalaman aplikasi yang mudah digunakan, indah, dan sesuai kebutuhan pengguna.',
                'category' => 'Kreatif',
            ]
        );

        unset($admin);
    }
}
