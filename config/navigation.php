<?php

/**
 * Menu sidebar vertikal untuk dashboard admin, Guru BK, dan siswa.
 *
 * Keys per group: group, section, icon?, items[]
 * Keys per item: label, route, params?, active?, icon?, title?
 *
 * section:
 * - main / core / platform → selalu terbuka
 * - yola → modul asesmen & RPL (kerja langsung)
 * - key → modul karier lanjut kuliah (PCR / RIASEC)
 */
return [
    'admin' => [
        [
            'group' => 'Utama',
            'section' => 'main',
            'icon' => 'home',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'home'],
            ],
        ],
        [
            'group' => 'Layanan BK',
            'section' => 'core',
            'icon' => 'chat',
            'items' => [
                ['label' => 'Konseling', 'title' => 'Konseling & Laporan', 'route' => 'admin.consultations.index', 'active' => 'admin.consultations.*', 'icon' => 'chat'],
                ['label' => 'Rapor BK', 'route' => 'admin.rapor.index', 'active' => 'admin.rapor.*', 'icon' => 'document'],
            ],
        ],
        [
            'group' => 'Data Master',
            'section' => 'core',
            'icon' => 'building',
            'items' => [
                ['label' => 'Sekolah', 'route' => 'admin.sekolah.index', 'active' => 'admin.sekolah.*', 'icon' => 'building'],
                ['label' => 'Kelas', 'route' => 'admin.kelas.index', 'active' => 'admin.kelas.*', 'icon' => 'academic'],
                ['label' => 'Guru BK', 'route' => 'admin.guru-bk.index', 'active' => 'admin.guru-bk.*', 'icon' => 'users'],
                ['label' => 'Master Pertanyaan', 'route' => 'admin.master-pertanyaan.index', 'active' => 'admin.master-pertanyaan.*', 'icon' => 'question'],
                ['label' => 'Kategori Artikel', 'route' => 'admin.kategori-postingan.index', 'active' => 'admin.kategori-postingan.*', 'icon' => 'tag'],
                ['label' => 'Artikel BK', 'route' => 'admin.postingan.index', 'active' => 'admin.postingan.*', 'icon' => 'newspaper'],
            ],
        ],
        [
            'group' => 'Karier Lanjut Kuliah (Key)',
            'section' => 'key',
            'icon' => 'academic',
            'items' => [
                ['label' => 'Kategori Minat RIASEC', 'route' => 'admin.interest-categories.index', 'active' => 'admin.interest-categories.*', 'icon' => 'tag'],
                ['label' => 'Program Studi PCR', 'route' => 'admin.program-studi.index', 'active' => 'admin.program-studi.*', 'icon' => 'academic'],
                ['label' => 'Bidang Karier', 'route' => 'admin.bidang-karier.index', 'active' => 'admin.bidang-karier.*', 'icon' => 'briefcase'],
            ],
        ],
        [
            'group' => 'Pengguna',
            'section' => 'core',
            'icon' => 'users',
            'items' => [
                ['label' => 'Approval Guru', 'title' => 'Approval Guru BK', 'route' => 'admin.approvals.index', 'active' => 'admin.approvals.*', 'icon' => 'check-badge'],
                ['label' => 'Manajemen Akun', 'title' => 'Manajemen Pengguna', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'users'],
                ['label' => 'Data Siswa', 'route' => 'admin.students.index', 'active' => 'admin.students.*', 'icon' => 'user'],
                ['label' => 'Kelas Bimbingan', 'route' => 'admin.guidance-classes.index', 'active' => 'admin.guidance-classes.*', 'icon' => 'rectangle-group'],
                ['label' => 'Perubahan Profil', 'title' => 'Perubahan Profil Guru', 'route' => 'admin.guru-profile-changes.index', 'active' => 'admin.guru-profile-changes.*', 'icon' => 'pencil'],
            ],
        ],
        [
            'group' => 'Informasi Umum',
            'section' => 'core',
            'icon' => 'briefcase',
            'items' => [
                ['label' => 'Informasi Karier', 'route' => 'admin.careers.index', 'active' => 'admin.careers.*', 'icon' => 'briefcase'],
            ],
        ],
        [
            'group' => 'Platform',
            'section' => 'platform',
            'icon' => 'grid',
            'items' => [
                ['label' => 'Log Aktivitas', 'route' => 'admin.activity-logs.index', 'active' => 'admin.activity-logs.*', 'icon' => 'clock'],
            ],
        ],
    ],
    'guru' => [
        [
            'group' => 'Utama',
            'section' => 'main',
            'icon' => 'home',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'guru.dashboard', 'active' => 'guru.dashboard', 'icon' => 'home'],
            ],
        ],
        [
            'group' => 'Layanan BK',
            'section' => 'core',
            'icon' => 'chat',
            'items' => [
                ['label' => 'Konseling', 'route' => 'guru.consultations.index', 'active' => 'guru.consultations.*', 'icon' => 'chat'],
                ['label' => 'Penilaian', 'title' => 'Laporan Penilaian', 'route' => 'guru.penilaian.index', 'active' => 'guru.penilaian.*', 'icon' => 'star'],
                ['label' => 'Angket', 'title' => 'Laporan Angket', 'route' => 'guru.angket.index', 'active' => 'guru.angket.*', 'icon' => 'clipboard'],
                ['label' => 'Rapor BK', 'route' => 'guru.rapor.index', 'active' => 'guru.rapor.*', 'icon' => 'document'],
                ['label' => 'Tryout', 'route' => 'guru.tryout.index', 'active' => 'guru.tryout.*', 'icon' => 'academic'],
                ['label' => 'Data Siswa', 'route' => 'guru.students.index', 'active' => 'guru.students.*', 'icon' => 'user'],
            ],
        ],
        [
            'group' => 'Asesmen & RPL (Yola)',
            'section' => 'yola',
            'icon' => 'beaker',
            'items' => [
                [
                    'label' => 'Soal Instrumen',
                    'route' => 'guru.instrument-questions.index',
                    'params' => ['module' => 'yola'],
                    'active' => 'guru.instrument-questions.*',
                    'match' => ['module' => 'yola'],
                    'icon' => 'beaker',
                ],
                [
                    'label' => 'Hasil Instrumen',
                    'route' => 'guru.instrument-results.index',
                    'params' => ['module' => 'yola'],
                    'active' => 'guru.instrument-results.*',
                    'match' => ['module' => 'yola'],
                    'icon' => 'chart',
                ],
                ['label' => 'Peta Sosiometri', 'route' => 'guru.sociometry.index', 'active' => 'guru.sociometry.*', 'icon' => 'chart'],
                ['label' => 'RPL Individu & Kelompok', 'route' => 'guru.rpls.index', 'active' => 'guru.rpls.*', 'icon' => 'book'],
                ['label' => 'Laporan Kelompok', 'route' => 'guru.group-reports.index', 'active' => 'guru.group-reports.*', 'icon' => 'document'],
                ['label' => 'Riwayat Siswa', 'route' => 'guru.student-histories.index', 'active' => 'guru.student-histories.*', 'icon' => 'user'],
                ['label' => 'Jurnal Bulanan', 'route' => 'guru.journals.index', 'active' => 'guru.journals.*', 'icon' => 'calendar'],
                ['label' => 'Statistik Layanan', 'route' => 'guru.service-statistics.index', 'active' => 'guru.service-statistics.*', 'icon' => 'chart'],
                ['label' => 'Feedback Siswa', 'route' => 'guru.feedback.index', 'active' => 'guru.feedback.*', 'icon' => 'chat'],
            ],
        ],
        [
            'group' => 'Karier Lanjut Kuliah (Key)',
            'section' => 'key',
            'icon' => 'academic',
            'items' => [
                [
                    'label' => 'Soal Minat Bakat RIASEC',
                    'route' => 'guru.instrument-questions.index',
                    'params' => ['module' => 'key'],
                    'active' => 'guru.instrument-questions.*',
                    'match' => ['module' => 'key'],
                    'icon' => 'beaker',
                ],
                [
                    'label' => 'Hasil Minat Bakat RIASEC',
                    'route' => 'guru.instrument-results.index',
                    'params' => ['module' => 'key'],
                    'active' => 'guru.instrument-results.*',
                    'match' => ['module' => 'key'],
                    'icon' => 'academic',
                ],
            ],
        ],
    ],
    'siswa' => [
        [
            'group' => 'Utama',
            'section' => 'main',
            'icon' => 'home',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'siswa.dashboard', 'active' => 'siswa.dashboard', 'icon' => 'home'],
            ],
        ],
        [
            'group' => 'Layanan BK',
            'section' => 'core',
            'icon' => 'chat',
            'items' => [
                ['label' => 'Konseling', 'route' => 'siswa.consultations.index', 'active' => ['siswa.consultations.*', 'siswa.consultation-requests.*'], 'icon' => 'chat'],
                ['label' => 'Chatbot Konseling', 'route' => 'siswa.chatbot.index', 'active' => 'siswa.chatbot.*', 'icon' => 'chat'],
                ['label' => 'Penilaian', 'title' => 'Penilaian Layanan', 'route' => 'siswa.penilaian.index', 'active' => 'siswa.penilaian.*', 'icon' => 'star'],
                ['label' => 'Angket BK', 'route' => 'siswa.angket.index', 'active' => 'siswa.angket.*', 'icon' => 'clipboard'],
                ['label' => 'Tryout', 'route' => 'siswa.tryout.index', 'active' => 'siswa.tryout.*', 'icon' => 'academic'],
                ['label' => 'Artikel BK', 'route' => 'siswa.postingan.index', 'active' => 'siswa.postingan.*', 'icon' => 'newspaper'],
            ],
        ],
        [
            'group' => 'Modul Tim Lain',
            'section' => 'yola',
            'icon' => 'beaker',
            'items' => [
                [
                    'label' => 'Strategi Belajar',
                    'title' => 'Instrumen Asesmen',
                    'route' => 'siswa.instruments.index',
                    'params' => ['category' => 'gaya_belajar'],
                    'active' => 'siswa.instruments.*',
                    'match' => ['category' => 'gaya_belajar'],
                    'icon' => 'book',
                ],
                [
                    'label' => 'Kepribadian',
                    'title' => 'Instrumen Asesmen',
                    'route' => 'siswa.instruments.index',
                    'params' => ['category' => 'kepribadian'],
                    'active' => 'siswa.instruments.*',
                    'match' => ['category' => 'kepribadian'],
                    'icon' => 'user',
                ],
                [
                    'label' => 'Masalah',
                    'title' => 'Instrumen Asesmen',
                    'route' => 'siswa.instruments.index',
                    'params' => ['category' => 'angket_masalah'],
                    'active' => 'siswa.instruments.*',
                    'match' => ['category' => 'angket_masalah'],
                    'icon' => 'clipboard',
                ],
                ['label' => 'Sosiometri', 'route' => 'siswa.sociometry.index', 'active' => 'siswa.sociometry.*', 'icon' => 'chart'],
                ['label' => 'Karier', 'title' => 'Informasi Karier', 'route' => 'siswa.careers.index', 'active' => 'siswa.careers.*', 'icon' => 'briefcase'],
            ],
        ],
        [
            'group' => 'Karier Lanjut Kuliah (Key)',
            'section' => 'key',
            'icon' => 'academic',
            'items' => [
                [
                    'label' => 'Minat Bakat RIASEC',
                    'title' => 'Asesmen minat + rekomendasi prodi PCR / bidang karier',
                    'route' => 'siswa.minat-bakat.index',
                    'active' => ['siswa.minat-bakat.*', 'siswa.instruments.hasil', 'siswa.instruments.hasil.pdf'],
                    'icon' => 'academic',
                ],
            ],
        ],
    ],
];
