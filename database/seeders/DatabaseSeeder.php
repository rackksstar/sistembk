<?php

namespace Database\Seeders;

use App\Models\CareerInfo;
use App\Models\ConsultationRequest;
use App\Models\GuidanceClass;
use App\Models\GuruBk;
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
use App\Models\SiswaSmk;
use App\Models\SociometryResponse;
use App\Models\Student;
use App\Models\User;
use App\Support\Mbti;
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

        // --- SMA ---
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

        $kelasXiiIpa = Kelas::query()->updateOrCreate(
            ['sekolah_id' => $sekolah->id, 'nama' => 'XII IPA 1'],
            [
                'jenjang' => 'SMA',
                'tingkatan' => 'XII',
            ]
        );

        $kelasXiIps = Kelas::query()->updateOrCreate(
            ['sekolah_id' => $sekolah->id, 'nama' => 'XI IPS 2'],
            [
                'jenjang' => 'SMA',
                'tingkatan' => 'XI',
            ]
        );

        // --- SMK ---
        $schoolSmk = School::query()->updateOrCreate(
            ['npsn' => '20260002'],
            [
                'name' => 'SMK Negeri 1 Contoh',
                'address' => 'Jl. Pendidikan No. 10',
            ]
        );

        $classSmkRpl = SchoolClass::query()->updateOrCreate(
            ['school_id' => $schoolSmk->id, 'name' => 'XII RPL 1'],
            ['level' => 'XII']
        );

        $sekolahSmk = Sekolah::query()->updateOrCreate(
            ['nama' => 'SMK Negeri 1 Contoh'],
            [
                'npsn' => '20260002',
                'alamat' => 'Jl. Pendidikan No. 10',
                'is_mou' => true,
                'paket_aktif' => 'Basic',
                'tanggal_aktivasi' => now()->toDateString(),
                'is_active' => true,
            ]
        );

        $kelasXiiRpl = Kelas::query()->updateOrCreate(
            ['sekolah_id' => $sekolahSmk->id, 'nama' => 'XII RPL 1'],
            [
                'jenjang' => 'SMK',
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

        // Admin SMK — pemisahan akun per jenjang (SMA vs SMK).
        User::query()->updateOrCreate(
            ['email' => 'admin.demo@bk.test'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('password'),
                'school' => $schoolSmk->name,
                'school_id' => $schoolSmk->id,
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
                'school' => $schoolSmk->name,
                'school_id' => $schoolSmk->id,
                'role' => User::ROLE_GURU,
                'status' => User::STATUS_PENDING,
                'email_verified_at' => null,
            ]
        );

        GuruBk::query()->updateOrCreate(
            ['user_id' => $pendingGuru->id],
            [
                'sekolah_id' => $sekolahSmk->id,
                'no_hp' => '081234567891',
                'nip' => '1987654321002',
                'jabatan' => 'Guru BK',
                'bidang_studi' => 'Bimbingan Konseling',
            ]
        );

        // Siswa SMK (XII RPL 1)
        $siswaSmkUser = User::query()->updateOrCreate(
            ['email' => 'siswa.smk@bk.test'],
            [
                'name' => 'Rina SMK',
                'password' => Hash::make('password'),
                'school' => $schoolSmk->name,
                'school_id' => $schoolSmk->id,
                'class_id' => $classSmkRpl->id,
                'role' => User::ROLE_SISWA,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]
        );

        $studentProfileSmk = Student::query()->updateOrCreate(
            ['nisn' => '0061234601'],
            [
                'user_id' => $siswaSmkUser->id,
                'kelas_id' => $kelasXiiRpl->id,
                'name' => $siswaSmkUser->name,
                'birth_date' => '2008-04-12',
                'school' => $sekolahSmk->nama,
                'jenis_kelamin' => 'P',
                'status_biodata' => 'lengkap',
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
                'kelas_id' => $kelasXiiIpa->id,
                'name' => $siswa->name,
                'birth_date' => '2008-05-14',
                'school' => $sekolah->nama,
                'jenis_kelamin' => 'L',
                'status_biodata' => 'lengkap',
            ]
        );

        SiswaSmk::query()->updateOrCreate(
            ['student_id' => $studentProfileSmk->id],
            [
                'user_id' => $siswaSmkUser->id,
                'name' => $studentProfileSmk->name,
                'nisn' => $studentProfileSmk->nisn,
                'sekolah' => $sekolahSmk->nama,
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'kelas' => $kelasXiiRpl->nama,
                'tahun_lulus' => now()->year,
                'nomor_hp' => '081234567899',
                'email' => $siswaSmkUser->email,
                'alamat' => 'Jl. Pendidikan No. 10',
                'keahlian' => ['HTML', 'CSS', 'Laravel', 'UI dasar'],
                'pengalaman' => 'Pernah membuat aplikasi pencatatan sederhana.',
                'status_kerja' => 'mencari_kerja',
                'siap_dihubungi' => true,
            ]
        );

        // Pemisahan SMA/SMK: profil pencari kerja SMK (SiswaSmk) hanya untuk
        // siswa SMK. Hapus bila sebelumnya sempat dibuat untuk siswa SMA.
        SiswaSmk::query()->where('student_id', $studentProfile->id)->delete();

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
                    'kelas_id' => ($index < 2 ? $kelasXiiIpa : $kelasXiIps)->id,
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

        // Minat Bakat Kerja memakai bank soal RIASEC (kategori minat_bakat) — nonaktifkan
        // soal klasik placeholder lama agar tidak bentrok dengan Talents Mapping.
        $legacyMinatKerja = [
            'Saya bersemangat saat mengerjakan aktivitas yang membutuhkan ide baru.',
            'Saya mudah menikmati pelajaran atau kegiatan yang menantang kemampuan berpikir.',
            'Saya memiliki aktivitas favorit yang ingin saya dalami sebagai rencana masa depan.',
        ];
        InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_KERJA)
            ->whereIn('question', $legacyMinatKerja)
            ->update(['is_active' => false]);

        $instrumentQuestions = [
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
            ['label' => 'Cukup Sesuai', 'score' => 3],
            ['label' => 'Sesuai', 'score' => 4],
            ['label' => 'Sangat Sesuai', 'score' => 5],
        ];

        foreach ($instrumentQuestions as $category => $questions) {
            foreach ($questions as $questionText) {
                InstrumentQuestion::query()->updateOrCreate(
                    ['category' => $category, 'question' => $questionText],
                    [
                        'section' => null,
                        'options' => $defaultOptions,
                        'is_active' => true,
                        'created_by' => $guru->id,
                    ]
                );
            }
        }

        // Tes Kepribadian ala 16Personalities: setiap soal adalah SATU pernyataan
        // yang dinilai dengan skala Likert persetujuan 1-5 (sama seperti
        // instrumen minat bakat). Tiap soal menyimpan dimensi + kutub yang
        // didukung jawaban "Setuju".
        $mbtiStatements = [
            'EI' => [
                ['Saya bersemangat ketika bisa mengobrol dan bekerja sama dengan banyak teman.', 'E'],
                ['Saya mudah memulai percakapan dengan orang yang baru dikenal.', 'E'],
                ['Saya lebih suka belajar kelompok daripada belajar sendirian.', 'E'],
                ['Saya merasa berenergi setelah menghabiskan waktu bersama teman-teman.', 'E'],
                ['Saya membutuhkan waktu menyendiri untuk mengembalikan energi setelah seharian beraktivitas dengan orang lain.', 'I'],
                ['Saya lebih nyaman menyampaikan pendapat secara tertulis daripada berbicara di depan banyak orang.', 'I'],
                ['Saya berpikir matang-matang dulu sebelum berbicara dalam diskusi.', 'I'],
                ['Saya lebih suka menghabiskan waktu luang sendirian atau hanya dengan satu-dua teman dekat.', 'I'],
            ],
            'SN' => [
                ['Saya lebih percaya pada pengalaman nyata daripada firasat atau dugaan.', 'S'],
                ['Saya memperhatikan detail-detail kecil saat mengerjakan tugas.', 'S'],
                ['Saya lebih suka mengikuti cara yang sudah terbukti berhasil.', 'S'],
                ['Saya fokus pada kejadian saat ini daripada memikirkan kemungkinan jauh ke depan.', 'S'],
                ['Saya senang membayangkan berbagai kemungkinan tentang masa depan.', 'N'],
                ['Saya tertarik mencari makna tersembunyi di balik suatu kejadian.', 'N'],
                ['Saya suka mencari pola dan hubungan antar hal yang terlihat tidak berkaitan.', 'N'],
                ['Saya sering mendapat ide baru ketika sedang melamun.', 'N'],
            ],
            'TF' => [
                ['Dalam mengambil keputusan, saya lebih mengutamakan logika daripada perasaan.', 'T'],
                ['Saya berani menyampaikan kritik yang jujur meskipun mungkin menyinggung perasaan orang lain.', 'T'],
                ['Saya menilai sesuatu berdasarkan adil atau tidaknya, bukan berdasarkan rasa kasihan.', 'T'],
                ['Saya tetap tenang dan objektif saat menghadapi konflik.', 'T'],
                ['Perasaan orang lain sangat memengaruhi keputusan saya.', 'F'],
                ['Saya sulit berkata tidak ketika teman meminta bantuan.', 'F'],
                ['Keharmonisan hubungan lebih penting bagi saya daripada memenangkan perdebatan.', 'F'],
                ['Saya mudah ikut merasakan kesedihan atau kegembiraan orang lain.', 'F'],
            ],
            'JP' => [
                ['Saya membuat rencana dan jadwal sebelum mengerjakan tugas besar.', 'J'],
                ['Kerapian dan keteraturan membuat saya nyaman saat belajar.', 'J'],
                ['Saya menyelesaikan tugas jauh sebelum tenggat waktu.', 'J'],
                ['Saya tidak suka perubahan rencana yang mendadak.', 'J'],
                ['Saya bekerja paling baik di bawah tekanan tenggat waktu.', 'P'],
                ['Saya lebih suka membiarkan pilihan tetap terbuka selama mungkin.', 'P'],
                ['Saya spontan dan mudah menyesuaikan diri dengan perubahan rencana.', 'P'],
                ['Saya mengerjakan tugas dengan fleksibel mengikuti suasana hati.', 'P'],
            ],
        ];

        // Soal kepribadian format lama (Likert tanpa dimensi, maupun pilihan
        // paksa dua kutub) dimatikan supaya tidak ikut terhitung dalam
        // skoring baru; riwayat jawaban lama tetap dipertahankan.
        InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_KEPRIBADIAN)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('mbti_axis')->orWhereNull('mbti_pole');
            })
            ->get(['id', 'options'])
            ->filter(fn ($question) => ! $question->isMbtiLikert())
            ->each(fn ($question) => $question->update(['is_active' => false]));

        foreach ($mbtiStatements as $axis => $statements) {
            foreach ($statements as [$statement, $pole]) {
                InstrumentQuestion::query()->updateOrCreate(
                    ['category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN, 'question' => $statement],
                    [
                        'section' => null,
                        'mbti_axis' => $axis,
                        'mbti_pole' => $pole,
                        'options' => InstrumentQuestion::KEPRIBADIAN_LIKERT_OPTIONS,
                        'is_active' => true,
                        'created_by' => $guru->id,
                    ]
                );
            }
        }

        // Strategi Belajar: 3 bagian (Perencanaan / Eksekusi / Refleksi) — selaras referensi.
        $strategiBelajarSections = [
            1 => [
                'Saya mempunyai tujuan belajar yang ingin saya capai.',
                'Saya mempunyai cara atau metode belajar saya sendiri.',
                'Ketika belajar, saya membuat target yang ingin saya capai, misalnya menyelesaikan 10 soal latihan dalam 1 jam.',
                'Ketika belajar, saya membuat daftar (to-do list) apa saja yang harus saya kerjakan.',
                'Saya percaya bahwa saya mampu memperoleh nilai yang bagus di sekolah.',
                'Nilai yang saya dapatkan di sekolah biasanya sesuai dengan apa yang saya perkirakan.',
                'Saya tahu apa yang akan saya dapatkan di masa depan jika saya belajar dengan baik sekarang.',
                'Saya menyukai sebagian besar aktivitas pembelajaran dan tugas yang diberikan di sekolah.',
                'Tujuan saya belajar adalah untuk meningkatkan kemampuan saya, tidak hanya mengejar nilai.',
                'Bagi saya, memperoleh nilai yang lebih baik dari teman-teman saya tidak begitu penting.',
            ],
            2 => [
                'Saat mengerjakan soal Matematika, saya selalu mengikuti langkah-langkah penyelesainnya secara bertahap.',
                'Saat membaca sebuah bacaan, saya memastikan saya mengerti bacaan tersebut dengan menggarisbawahi bagian penting atau memastikan gagasan utamanya.',
                'Saya sering membuat mindmap, peta konsep, atau catatan/rangkuman untuk membantu memahami konsep yang dipelajari.',
                'Saya membuat jadwal belajar secara teratur untuk mengulang kembali materi dan mengerjakan tugas sekolah.',
                'Sebelum mulai belajar, saya memastikan lingkungan saya mendukung konsentrasi belajar saya.',
                'Ketika ada materi yang tidak saya mengerti, saya tahu harus bertanya apa dan kepada siapa.',
                'Ketika saya bosan, saya mencoba membuat tugas saya lebih menarik, misalnya dengan mengajak teman saya belajar bersama.',
                'Saya memberikan reward kepada diri saya saat belajar, misalnya saya boleh membuka media sosial setelah tugas saya selesai.',
                'Ketika saya mencoba sebuah cara belajar baru, saya memonitor seberapa tinggi nilai yang saya peroleh setelah menerapkan cara belajar tersebut.',
                'Saya membuat catatan konsep-konsep apa saja yang belum saya kuasai dan perlu saya pelajari kembali.',
            ],
            3 => [
                'Menurut saya, pembelajaran saya berhasil jika nilai yang saya dapatkan bisa mencapai atau lebih dari target nilai yang sudah saya tentukan.',
                'Saya selalu memonitor perkembangan nilai saya dari waktu ke waktu.',
                'Ketika saya mendapat nilai yang kurang memuaskan, saya bisa mengidentifikasi apa penyebabnya.',
                'Ketika saya mendapat nilai yang kurang memuaskan, saya akan menurunkan target nilai saya untuk tugas/ulangan berikutnya.',
                'Ketika saya mendapat nilai yang kurang memuaskan, saya akan mengubah cara belajar saya.',
                'Saya merasa saya sebenarnya mampu, meskipun kadang nilai saya kurang memuaskan.',
                'Saya merasa puas dengan cara belajar saya saat ini.',
                'Ketika sebuah strategi belajar telah memberikan nilai yang memuaskan, maka saya akan melanjutkan strategi tersebut.',
                'Saya merasa jika saya belajar, maka nilai saya akan lebih bagus daripada jika saya tidak belajar.',
                'Saya jarang menunda-nunda belajar karena saya suka belajar.',
            ],
        ];

        $strategiTexts = [];
        foreach ($strategiBelajarSections as $section => $questions) {
            foreach ($questions as $questionText) {
                $strategiTexts[] = $questionText;
                InstrumentQuestion::query()->updateOrCreate(
                    ['category' => InstrumentQuestion::CATEGORY_GAYA_BELAJAR, 'question' => $questionText],
                    [
                        'section' => $section,
                        'options' => $defaultOptions,
                        'is_active' => true,
                        'created_by' => $guru->id,
                    ]
                );
            }
        }

        // Nonaktifkan soal gaya belajar lama yang bukan bagian Strategi Belajar 3 sesi.
        InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_GAYA_BELAJAR)
            ->whereNotIn('question', $strategiTexts)
            ->update(['is_active' => false]);

        $sampleQuestions = InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_KERJA)
            ->where('is_active', true)
            ->get();

        if ($sampleQuestions->isNotEmpty()) {
            DB::transaction(function () use ($sampleQuestions, $siswa) {
                $submission = InstrumentSubmission::query()->updateOrCreate(
                    ['student_id' => $siswa->id, 'category' => InstrumentQuestion::CATEGORY_MINAT_KERJA],
                    [
                        'total_score' => 10,
                        'percentage' => 66.67,
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
            ->where('is_active', true)
            ->whereNotNull('mbti_axis')
            ->whereNotNull('mbti_pole')
            ->oldest()
            ->get()
            ->values();

        if ($personalityQuestions->isNotEmpty()) {
            $likertOptions = InstrumentQuestion::KEPRIBADIAN_LIKERT_OPTIONS;

            foreach ($studentUsers->take(3) as $studentUser) {
                // Jawaban demo yang deterministik per siswa supaya kode MBTI
                // tiap akun berbeda-beda tapi tetap konsisten saat di-seed ulang.
                $ballots = [];
                $answerLabels = [];

                foreach ($personalityQuestions as $idx => $question) {
                    $score = (($studentUser->id + $idx) % 5) + 1;
                    $ballots[] = ['pole' => $question->mbti_pole, 'score' => $score];
                    $answerLabels[$question->id] = [
                        'label' => $likertOptions[$score - 1]['label'],
                        'score' => $score,
                    ];
                }

                $mbti = Mbti::scoreLikert($ballots);

                $submission = InstrumentSubmission::query()->updateOrCreate(
                    ['student_id' => $studentUser->id, 'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN],
                    [
                        'total_score' => array_sum(array_column($ballots, 'score')),
                        'category_scores' => [
                            'code' => $mbti['code'],
                            'type' => $mbti['type'],
                            'tally' => $mbti['tally'],
                            'detail' => Mbti::detailFor($mbti['code']),
                        ],
                        'percentage' => $mbti['confidence'],
                        'result_label' => $mbti['code'].' — '.$mbti['type']['name'],
                        'result_description' => $mbti['type']['description'],
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
                            'answer_label' => $answerLabels[$question->id]['label'] ?? '',
                            'score' => $answerLabels[$question->id]['score'] ?? 0,
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

        $rplIndividu = Rpl::query()->updateOrCreate(
            ['teacher_id' => $guru->id, 'title' => 'RPL Individu Manajemen Waktu Belajar'],
            [
                'type' => Rpl::TYPE_INDIVIDU,
                'class_id' => $classXiiIpa->id,
                'student_id' => $siswa->id,
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

        $rplKelompok = Rpl::query()->updateOrCreate(
            ['teacher_id' => $guru->id, 'title' => 'RPL Kelompok Komunikasi Asertif'],
            [
                'type' => Rpl::TYPE_KELOMPOK,
                'class_id' => $classXiiIpa->id,
                'service_date' => now()->addWeeks(2)->toDateString(),
                'target' => 'Kelompok siswa kelas bimbingan karier',
                'tujuan' => 'Siswa mampu menyampaikan pendapat secara jelas, sopan, dan menghargai orang lain.',
                'materi' => 'Konsep komunikasi asertif, contoh kalimat asertif, dan latihan respons sosial.',
                'metode' => 'Bimbingan kelompok, diskusi, permainan peran, dan umpan balik.',
                'evaluasi' => 'Guru BK mengamati partisipasi siswa dan meminta refleksi singkat setelah kegiatan.',
            ]
        );

        // Anggota kelompok demo: Alya + Bima (kelas XII IPA 1).
        $rplKelompok->groupStudents()->sync(
            $studentUsers->slice(1, 2)->pluck('id')->all()
        );

        unset($rplIndividu);

        // Akun demo tambahan bergantian SMA/SMK agar terpisah per jenjang.
        User::factory()
            ->count(6)
            ->sequence(
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED, 'school' => $school->name, 'school_id' => $school->id, 'class_id' => $classXiiIpa->id],
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED, 'school' => $schoolSmk->name, 'school_id' => $schoolSmk->id, 'class_id' => $classSmkRpl->id],
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED, 'school' => $school->name, 'school_id' => $school->id, 'class_id' => $classXiIps->id],
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED, 'school' => $schoolSmk->name, 'school_id' => $schoolSmk->id, 'class_id' => $classSmkRpl->id],
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED, 'school' => $school->name, 'school_id' => $school->id, 'class_id' => $classXiIps->id],
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED, 'school' => $schoolSmk->name, 'school_id' => $schoolSmk->id, 'class_id' => $classSmkRpl->id],
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

        $this->call([
            InterestCategorySeeder::class,
            MinatQuestionSeeder::class,
            ProgramStudiPcrSeeder::class,
            CareerFieldSeeder::class,
        ]);

        unset($admin);
    }
}
