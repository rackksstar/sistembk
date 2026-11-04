<?php

return [
    /*
     * Ambang batas persentase skor per bagian untuk Asesmen Masalah.
     * Selaras dengan InstrumentSubmissionController@scoreResult:
     * >=70 Prioritas Tinggi, >=40 Perlu Dipantau, sisanya Ringan.
     * Skor tinggi = banyak masalah yang diakui siswa.
     */
    'thresholds' => [
        'tinggi' => 70,
        'sedang' => 40,
    ],

    'levels' => [
        'ringan' => ['label' => 'Ringan', 'color' => 'emerald', 'icon' => 'check'],
        'sedang' => ['label' => 'Perlu Dipantau', 'color' => 'amber', 'icon' => 'check'],
        'tinggi' => ['label' => 'Prioritas Tinggi', 'color' => 'red', 'icon' => 'exclamation'],
    ],

    'sections' => [
        1 => [ // Pribadi (soal 1-20)
            'ringan' => [
                'description' => 'Kondisi pribadimu tampak stabil. Pertahankan kebiasaan baik seperti ibadah, kejujuran, dan pengelolaan emosi.',
                'tips' => [
                    'Pertahankan rutinitas ibadah dan refleksi diri harian.',
                    'Terus latih keterbukaan menerima kritik dan mengakui kesalahan.',
                    'Jaga pola hidup sehat dan kelola waktu belajar-bermain dengan seimbang.',
                ],
            ],
            'sedang' => [
                'description' => 'Ada beberapa hal pribadi yang perlu kamu perhatikan, seperti kedisiplinan, emosi, atau kepercayaan diri. Coba kenali pemicunya dan diskusikan dengan orang terdekat.',
                'tips' => [
                    'Tulis hal yang membuatmu bersalah atau sulit dikendalikan, lalu cari satu langkah kecil memperbaikinya.',
                    'Latih sikap asertif: sampaikan kebutuhanmu dengan jujur tanpa menyakiti orang lain.',
                    'Minta umpan balik dari guru atau teman tentang satu kebiasaan yang ingin kamu ubah.',
                ],
            ],
            'tinggi' => [
                'description' => 'Banyak pernyataan pribadi yang kamu akui. Ini sinyal untuk segera bercerita dan mendapat pendampingan Guru BK agar tidak menumpuk.',
                'tips' => [
                    'Segera jadwalkan konseling dengan Guru BK untuk memetakan masalah pribadimu.',
                    'Ceritakan kondisimu pada orang tua atau wali yang kamu percaya.',
                    'Buat jurnal harian emosi dan kebiasaan ibadah untuk dipantau bersama Guru BK.',
                ],
            ],
        ],

        2 => [ // Sosial (soal 21-31)
            'ringan' => [
                'description' => 'Interaksi sosialmu tampak baik. Pertahankan sopan santun dan keberanian bergaul di sekolah maupun lingkungan rumah.',
                'tips' => [
                    'Pertahankan sapaan sopan dan empati pada teman serta tetangga.',
                    'Imbangi interaksi media sosial dengan obrolan langsung.',
                    'Ikut satu kegiatan sosial agar lingkaran pertemananmu tetap luas.',
                ],
            ],
            'sedang' => [
                'description' => 'Ada hambatan sosial yang mulai terlihat, misalnya sulit bergaul atau belum paham bullying dan etika sosial. Pelajari polanya sedikit demi sedikit.',
                'tips' => [
                    'Pelajari ciri bullying dan cara menyikapinya, lalu simpan kontak bantuan yang bisa dihubungi.',
                    'Latih satu keberanian kecil tiap minggu, misalnya menyapa teman baru.',
                    'Kurangi waktu layar dan ganti dengan satu aktivitas tatap muka.',
                ],
            ],
            'tinggi' => [
                'description' => 'Kamu menandai banyak kesulitan sosial. Jangan dihadapi sendiri — Guru BK siap membantu mencarikan lingkungan yang aman dan strategi bergaul.',
                'tips' => [
                    'Laporkan pengalaman bullying atau konflik sosial kepada Guru BK/orang tua.',
                    'Minta difasilitasi mediasi bila ada masalah dengan teman.',
                    'Ikuti kegiatan kelompok kecil yang didampingi guru untuk melatih keberanian sosial.',
                ],
            ],
        ],

        3 => [ // Belajar (soal 32-43)
            'ringan' => [
                'description' => 'Kebiasaan belajarmu sudah cukup tertata. Pertahankan cara belajar yang benar dan pemanfaatan sumber serta teknologi belajar.',
                'tips' => [
                    'Pertahankan jadwal belajar rutin, bukan hanya saat ada tes.',
                    'Terus gunakan sumber belajar dan teknologi untuk memperdalam materi.',
                    'Coba sesekali belajar kelompok untuk saling menguatkan.',
                ],
            ],
            'sedang' => [
                'description' => 'Ada kebiasaan belajar yang perlu dibenahi, misalnya menunda tugas, mengantuk, atau belum tahu gaya belajar yang cocok. Fokus pada satu perbaikan dulu.',
                'tips' => [
                    'Terapkan teknik 25 menit fokus + 5 menit istirahat untuk melawan rasa malas dan kantuk.',
                    'Kenali gaya belajarmu (visual/auditori/kinestetik) lalu sesuaikan strateginya.',
                    'Buat daftar tugas dengan tenggat dan coret setiap yang selesai agar tidak menunda.',
                ],
            ],
            'tinggi' => [
                'description' => 'Banyak pernyataan kesulitan belajar yang kamu akui. Diskusikan dengan Guru BK dan guru mata pelajaran agar mendapat strategi dan fasilitas yang tepat.',
                'tips' => [
                    'Minta pemetaan gaya belajar dan pendampingan belajar kepada Guru BK.',
                    'Sampaikan kendala fasilitas atau pemahaman materi kepada guru/orang tua.',
                    'Susun rencana belajar harian sederhana dan evaluasi mingguan bersama guru.',
                ],
            ],
        ],

        4 => [ // Karir (soal 44-50)
            'ringan' => [
                'description' => 'Arah karirmu mulai jelas. Kamu sudah mengenali hubungan hobi, bakat, minat, dan pilihan jurusan atau kegiatan.',
                'tips' => [
                    'Perdalam informasi jurusan, ekstrakurikuler, atau bidang kewirausahaan yang kamu minati.',
                    'Cari informasi beasiswa atau magang sejak dini.',
                    'Diskusikan rencanamu dengan Guru BK agar semakin mantap.',
                ],
            ],
            'sedang' => [
                'description' => 'Kamu masih ragu pada beberapa pilihan peminatan, jurusan, atau ekstrakurikuler. Wajar — gali informasi lebih banyak sebelum memutuskan.',
                'tips' => [
                    'Ikuti asesmen Minat Bakat lalu cocokkan hasilnya dengan pilihan jurusanmu.',
                    'Tanya kakak kelas, guru, atau praktisi tentang bidang yang kamu minati.',
                    'Coba satu ekstrakurikuler selama sebulan sebelum menilai cocok atau tidak.',
                ],
            ],
            'tinggi' => [
                'description' => 'Kebingungan karir cukup tinggi. Jangan memutuskan terburu-buru — minta pendampingan Guru BK untuk memetakan minat, kemampuan, dan pilihan yang realistis.',
                'tips' => [
                    'Jadwalkan konseling karir dengan Guru BK untuk membahas pilihan peminatan/jurusan.',
                    'Cari info beasiswa dan alternatif biaya pendidikan agar pilihan tetap terbuka.',
                    'Jika harus bekerja, diskusikan pembagian waktu belajar-kerja yang aman dengan guru dan orang tua.',
                ],
            ],
        ],
    ],
];
