<?php

/**
 * Menu sidebar vertikal untuk dashboard admin, Guru BK, dan siswa.
 */
return [
    'admin' => [
        [
            'group' => 'Utama',
            'section' => 'main',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ],
        ],
        [
            'group' => 'Layanan BK',
            'section' => 'core',
            'items' => [
                ['label' => 'Konseling & Laporan', 'route' => 'admin.consultations.index', 'active' => 'admin.consultations.*'],
                ['label' => 'Rapor BK', 'route' => 'admin.rapor.index', 'active' => 'admin.rapor.*'],
            ],
        ],
        [
            'group' => 'Data Master',
            'section' => 'core',
            'items' => [
                ['label' => 'Sekolah', 'route' => 'admin.sekolah.index', 'active' => 'admin.sekolah.*'],
                ['label' => 'Kelas', 'route' => 'admin.kelas.index', 'active' => 'admin.kelas.*'],
                ['label' => 'Guru BK', 'route' => 'admin.guru-bk.index', 'active' => 'admin.guru-bk.*'],
                ['label' => 'Master Pertanyaan', 'route' => 'admin.master-pertanyaan.index', 'active' => 'admin.master-pertanyaan.*'],
                ['label' => 'Kategori Postingan', 'route' => 'admin.kategori-postingan.index', 'active' => 'admin.kategori-postingan.*'],
                ['label' => 'Postingan Artikel', 'route' => 'admin.postingan.index', 'active' => 'admin.postingan.*'],
            ],
        ],
        [
            'group' => 'Pengguna & Siswa',
            'section' => 'core',
            'items' => [
                ['label' => 'Approval Guru BK', 'route' => 'admin.approvals.index', 'active' => 'admin.approvals.*'],
                ['label' => 'Manajemen Pengguna', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
                ['label' => 'Data Siswa', 'route' => 'admin.students.index', 'active' => 'admin.students.*'],
                ['label' => 'Kelas Bimbingan', 'route' => 'admin.guidance-classes.index', 'active' => 'admin.guidance-classes.*'],
                ['label' => 'Perubahan Profil Guru', 'route' => 'admin.guru-profile-changes.index', 'active' => 'admin.guru-profile-changes.*'],
            ],
        ],
        [
            'group' => 'Modul Tim Lain',
            'section' => 'other',
            'items' => [
                ['label' => 'Informasi Karier', 'route' => 'admin.careers.index', 'active' => 'admin.careers.*'],
            ],
        ],
        [
            'group' => 'Platform',
            'section' => 'platform',
            'items' => [
                ['label' => 'Log Aktivitas', 'route' => 'admin.activity-logs.index', 'active' => 'admin.activity-logs.*'],
            ],
        ],
    ],
    'guru' => [
        [
            'group' => 'Utama',
            'section' => 'main',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'guru.dashboard', 'active' => 'guru.dashboard'],
            ],
        ],
        [
            'group' => 'Layanan BK',
            'section' => 'core',
            'items' => [
                ['label' => 'Konseling', 'route' => 'guru.consultations.index', 'active' => 'guru.consultations.*'],
                ['label' => 'Laporan Penilaian', 'route' => 'guru.penilaian.index', 'active' => 'guru.penilaian.*'],
                ['label' => 'Laporan Angket', 'route' => 'guru.angket.index', 'active' => 'guru.angket.*'],
                ['label' => 'Rapor BK', 'route' => 'guru.rapor.index', 'active' => 'guru.rapor.*'],
                ['label' => 'Tryout', 'route' => 'guru.tryout.index', 'active' => 'guru.tryout.*'],
            ],
        ],
        [
            'group' => 'Data Siswa',
            'section' => 'core',
            'items' => [
                ['label' => 'Data Siswa', 'route' => 'guru.students.index', 'active' => 'guru.students.*'],
            ],
        ],
        [
            'group' => 'Modul Tim Lain',
            'section' => 'other',
            'items' => [
                ['label' => 'Soal Instrumen', 'route' => 'guru.instrument-questions.index', 'active' => 'guru.instrument-questions.*'],
                ['label' => 'Hasil Instrumen', 'route' => 'guru.instrument-results.index', 'active' => 'guru.instrument-results.*'],
                ['label' => 'Peta Sosiometri', 'route' => 'guru.sociometry.index', 'active' => 'guru.sociometry.*'],
                ['label' => 'RPL', 'route' => 'guru.rpls.index', 'active' => 'guru.rpls.*'],
                ['label' => 'Jurnal Bulanan', 'route' => 'guru.journals.index', 'active' => 'guru.journals.*'],
            ],
        ],
    ],
    'siswa' => [
        [
            'group' => 'Utama',
            'section' => 'main',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'siswa.dashboard', 'active' => 'siswa.dashboard'],
            ],
        ],
        [
            'group' => 'Layanan BK',
            'section' => 'core',
            'items' => [
                ['label' => 'Konseling', 'route' => 'siswa.consultations.index', 'active' => ['siswa.consultations.*', 'siswa.consultation-requests.*']],
                ['label' => 'Penilaian Layanan', 'route' => 'siswa.penilaian.index', 'active' => 'siswa.penilaian.*'],
                ['label' => 'Angket BK', 'route' => 'siswa.angket.index', 'active' => 'siswa.angket.*'],
                ['label' => 'Tryout', 'route' => 'siswa.tryout.index', 'active' => 'siswa.tryout.*'],
                ['label' => 'Artikel BK', 'route' => 'siswa.postingan.index', 'active' => 'siswa.postingan.*'],
            ],
        ],
        [
            'group' => 'Modul Tim Lain',
            'section' => 'other',
            'items' => [
                ['label' => 'Instrumen Asesmen', 'route' => 'siswa.instruments.index', 'active' => 'siswa.instruments.*'],
                ['label' => 'Sosiometri', 'route' => 'siswa.sociometry.index', 'active' => 'siswa.sociometry.*'],
                ['label' => 'Informasi Karier', 'route' => 'siswa.careers.index', 'active' => 'siswa.careers.*'],
            ],
        ],
    ],
];
