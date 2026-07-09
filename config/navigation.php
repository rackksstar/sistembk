<?php

/**
 * Menu sidebar dashboard untuk admin, Guru BK, dan siswa.
 */
return [
    'admin' => [
        [
            'group' => 'Utama',
            'icon' => 'home',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
            ],
        ],
        [
            'group' => 'Layanan BK (Core)',
            'icon' => 'heart-handshake',
            'items' => [
                ['label' => 'Konseling & Laporan', 'route' => 'admin.consultations.index', 'active' => 'admin.consultations.*', 'icon' => 'messages'],
                ['label' => 'Rapor BK', 'route' => 'admin.rapor.index', 'active' => 'admin.rapor.*', 'icon' => 'file-chart'],
            ],
        ],
        [
            'group' => 'Data Master (Core)',
            'icon' => 'database',
            'items' => [
                ['label' => 'Sekolah', 'route' => 'admin.sekolah.index', 'active' => 'admin.sekolah.*', 'icon' => 'school'],
                ['label' => 'Kelas', 'route' => 'admin.kelas.index', 'active' => 'admin.kelas.*', 'icon' => 'door-open'],
                ['label' => 'Guru BK', 'route' => 'admin.guru-bk.index', 'active' => 'admin.guru-bk.*', 'icon' => 'teacher'],
                ['label' => 'Master Pertanyaan', 'route' => 'admin.master-pertanyaan.index', 'active' => 'admin.master-pertanyaan.*', 'icon' => 'clipboard-question'],
                ['label' => 'Kategori Postingan', 'route' => 'admin.kategori-postingan.index', 'active' => 'admin.kategori-postingan.*', 'icon' => 'tags'],
                ['label' => 'Postingan Artikel', 'route' => 'admin.postingan.index', 'active' => 'admin.postingan.*', 'icon' => 'newspaper'],
            ],
        ],
        [
            'group' => 'Pengguna & Siswa',
            'icon' => 'users',
            'items' => [
                ['label' => 'Approval Guru BK', 'route' => 'admin.approvals.index', 'active' => 'admin.approvals.*', 'icon' => 'user-check'],
                ['label' => 'Manajemen Pengguna', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'users'],
                ['label' => 'Data Siswa', 'route' => 'admin.students.index', 'active' => 'admin.students.*', 'icon' => 'graduation-cap'],
                ['label' => 'Kelas Bimbingan', 'route' => 'admin.guidance-classes.index', 'active' => 'admin.guidance-classes.*', 'icon' => 'network'],
                ['label' => 'Log Aktivitas', 'route' => 'admin.activity-logs.index', 'active' => 'admin.activity-logs.*', 'icon' => 'activity'],
            ],
        ],
        [
            'group' => 'Karier',
            'icon' => 'briefcase',
            'items' => [
                ['label' => 'Informasi Karier', 'route' => 'admin.careers.index', 'active' => 'admin.careers.*', 'icon' => 'briefcase'],
            ],
        ],
        [
            'group' => 'Akun',
            'icon' => 'settings',
            'items' => [
                ['label' => 'Profil', 'route' => 'profile.edit', 'active' => 'profile.*', 'icon' => 'user-cog'],
            ],
        ],
    ],
    'guru' => [
        [
            'group' => 'Utama',
            'icon' => 'home',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'guru.dashboard', 'active' => 'guru.dashboard', 'icon' => 'layout-dashboard'],
            ],
        ],
        [
            'group' => 'Layanan BK (Core)',
            'icon' => 'heart-handshake',
            'items' => [
                ['label' => 'Konseling', 'route' => 'guru.consultations.index', 'active' => 'guru.consultations.*', 'icon' => 'messages'],
                ['label' => 'Laporan Penilaian', 'route' => 'guru.penilaian.index', 'active' => 'guru.penilaian.*', 'icon' => 'star'],
                ['label' => 'Laporan Angket', 'route' => 'guru.angket.index', 'active' => 'guru.angket.*', 'icon' => 'clipboard-list'],
                ['label' => 'Rapor BK', 'route' => 'guru.rapor.index', 'active' => 'guru.rapor.*', 'icon' => 'file-chart'],
                ['label' => 'Tryout', 'route' => 'guru.tryout.index', 'active' => 'guru.tryout.*', 'icon' => 'timer'],
            ],
        ],
        [
            'group' => 'Data Siswa',
            'icon' => 'graduation-cap',
            'items' => [
                ['label' => 'Data Siswa', 'route' => 'guru.students.index', 'active' => 'guru.students.*', 'icon' => 'graduation-cap'],
            ],
        ],
        [
            'group' => 'Modul Pendukung',
            'icon' => 'sparkles',
            'items' => [
                ['label' => 'Soal Instrumen', 'route' => 'guru.instrument-questions.index', 'active' => 'guru.instrument-questions.*', 'icon' => 'clipboard-question'],
                ['label' => 'Hasil Instrumen', 'route' => 'guru.instrument-results.index', 'active' => 'guru.instrument-results.*', 'icon' => 'chart-line'],
                ['label' => 'Peta Sosiometri', 'route' => 'guru.sociometry.index', 'active' => 'guru.sociometry.*', 'icon' => 'network'],
                ['label' => 'RPL', 'route' => 'guru.rpls.index', 'active' => 'guru.rpls.*', 'icon' => 'book-open'],
            ],
        ],
        [
            'group' => 'Akun',
            'icon' => 'settings',
            'items' => [
                ['label' => 'Profil', 'route' => 'profile.edit', 'active' => 'profile.*', 'icon' => 'user-cog'],
            ],
        ],
    ],
    'siswa' => [
        [
            'group' => 'Utama',
            'icon' => 'home',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'siswa.dashboard', 'active' => 'siswa.dashboard', 'icon' => 'layout-dashboard'],
            ],
        ],
        [
            'group' => 'Layanan BK (Core)',
            'icon' => 'heart-handshake',
            'items' => [
                ['label' => 'Konseling', 'route' => 'siswa.consultations.index', 'active' => ['siswa.consultations.*', 'siswa.consultation-requests.*'], 'icon' => 'messages'],
                ['label' => 'Penilaian Layanan', 'route' => 'siswa.penilaian.index', 'active' => 'siswa.penilaian.*', 'icon' => 'star'],
                ['label' => 'Angket BK', 'route' => 'siswa.angket.index', 'active' => 'siswa.angket.*', 'icon' => 'clipboard-list'],
                ['label' => 'Tryout', 'route' => 'siswa.tryout.index', 'active' => 'siswa.tryout.*', 'icon' => 'timer'],
            ],
        ],
        [
            'group' => 'Konten BK',
            'icon' => 'newspaper',
            'items' => [
                ['label' => 'Artikel BK', 'route' => 'siswa.postingan.index', 'active' => 'siswa.postingan.*', 'icon' => 'newspaper'],
            ],
        ],
        [
            'group' => 'Karier & Asesmen',
            'icon' => 'briefcase',
            'items' => [
                ['label' => 'Instrumen Asesmen', 'route' => 'siswa.instruments.index', 'active' => 'siswa.instruments.*', 'icon' => 'clipboard-question'],
                ['label' => 'Sosiometri', 'route' => 'siswa.sociometry.index', 'active' => 'siswa.sociometry.*', 'icon' => 'network'],
                ['label' => 'Informasi Karier', 'route' => 'siswa.careers.index', 'active' => 'siswa.careers.*', 'icon' => 'briefcase'],
            ],
        ],
    ],
];
