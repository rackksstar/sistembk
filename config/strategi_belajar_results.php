<?php

return [
    /*
     * Ambang batas persentase skor per bagian untuk menentukan level hasil.
     * Selaras dengan skema 3 tingkat yang sudah dipakai instrumen lain
     * (InstrumentSubmissionController@scoreResult): >=70 baik, >=40 cukup, sisanya kurang.
     */
    'thresholds' => [
        'baik' => 70,
        'cukup' => 40,
    ],

    'levels' => [
        'kurang' => ['label' => 'Perlu Ditingkatkan', 'color' => 'red', 'icon' => 'exclamation'],
        'cukup' => ['label' => 'Cukup Baik', 'color' => 'amber', 'icon' => 'check'],
        'baik' => ['label' => 'Memuaskan', 'color' => 'emerald', 'icon' => 'check'],
    ],

    'sections' => [
        1 => [ // Perencanaan Belajar
            'kurang' => [
                'description' => 'Wah, sepertinya kemampuan kamu untuk mempersiapkan belajarmu masih belum matang, nih. Agar belajarmu efektif, kamu perlu membuat perencanaan yang matang, seperti membuat daftar tugas-tugas yang dikerjakan, memilih metode belajar yang sesuai dengan tugas yang kamu hadapi, dan membangun motivasi belajarmu.',
                'tips' => [
                    'Tentukan tujuan apa yang ingin kamu capai di masa depan biar semangat!',
                    'Buat daftar tugas-tugas yang perlu kamu kerjakan di setiap sesi belajar dan tentukan target waktunya',
                    'Coba dan temukan metode belajar yang paling cocok misalnya mengerjakan soal dulu, lalu menonton video dari konsep yang masih belum kamu kuasai, atau lainnya',
                    'Tanamkan growth mindset: tujuanmu belajar adalah untuk membuat dirimu jadi lebih baik. Jika hasilnya masih belum memuaskan, jadikan itu sebagai kesempatan untuk memperbaiki diri dan jangan menyerah!',
                ],
            ],
            'cukup' => [
                'description' => 'Kemampuan kamu untuk merencanakan proses belajarmu sudah cukup baik. Kamu sudah mulai punya gambaran tujuan dan target belajar, hanya saja masih perlu lebih konsisten dalam menerapkannya supaya perencanaan yang kamu buat benar-benar membantu proses belajarmu.',
                'tips' => [
                    'Tuliskan tujuan dan target belajarmu secara rutin, jangan hanya dipikirkan di kepala.',
                    'Evaluasi apakah metode belajar yang kamu pakai selama ini sudah sesuai dengan gaya belajarmu.',
                    'Coba buat to-do list belajar setiap hari agar kamu semakin terbiasa merencanakan sebelum belajar.',
                    'Pertahankan rasa percaya diri terhadap kemampuanmu, sambil terus mengasah perencanaan yang lebih matang.',
                ],
            ],
            'baik' => [
                'description' => 'Selamat! Kemampuan kamu untuk merencanakan proses belajarmu sudah sangat baik. Kamu sudah mempunyai tujuan belajar yang jelas, mampu menyusun target yang terukur, serta percaya diri dengan kemampuanmu sendiri. Pertahankan kebiasaan baik ini, ya.',
                'tips' => [
                    'Terus konsisten menetapkan tujuan dan target belajar setiap kali akan memulai sesi belajar.',
                    'Sesekali evaluasi kembali target yang sudah kamu buat, apakah masih relevan dengan kondisimu saat ini.',
                    'Bagikan cara perencanaan belajarmu ke teman yang masih kesulitan merencanakan belajarnya.',
                    'Jangan berhenti belajar hal baru meskipun perencanaanmu sudah matang.',
                ],
            ],
        ],

        2 => [ // Eksekusi Belajar
            'kurang' => [
                'description' => 'Wah, sepertinya kemampuan kamu untuk mengeksekusi proses belajarmu masih perlu ditingkatkan, nih. Kamu masih perlu melatih diri untuk menerapkan strategi belajar yang efektif, seperti mengikuti langkah-langkah penyelesaian soal secara runtut, membuat catatan atau rangkuman, hingga menjaga konsentrasi selama belajar.',
                'tips' => [
                    'Biasakan mengikuti langkah-langkah penyelesaian soal secara bertahap, jangan buru-buru ke jawaban akhir.',
                    'Saat membaca materi, garis bawahi atau catat bagian yang menurutmu penting.',
                    'Buat mindmap atau rangkuman sederhana untuk membantu memahami konsep yang rumit.',
                    'Susun jadwal belajar yang teratur agar kamu terbiasa mengulang materi secara berkala.',
                    'Pastikan tempat belajarmu nyaman dan bebas dari gangguan sebelum mulai belajar.',
                    'Jangan ragu bertanya kepada guru atau teman saat menemui materi yang sulit dipahami.',
                ],
            ],
            'cukup' => [
                'description' => 'Kemampuan kamu untuk mengeksekusi proses belajarmu sudah cukup baik. Kamu sudah mulai menerapkan beberapa strategi belajar yang efektif, namun masih perlu lebih konsisten agar hasil belajarmu semakin optimal.',
                'tips' => [
                    'Coba lebih rutin membuat catatan atau rangkuman setiap selesai belajar satu topik.',
                    'Pertahankan kebiasaan menyusun jadwal belajar, dan usahakan untuk selalu mengikutinya.',
                    'Beri variasi pada cara belajarmu supaya tidak mudah bosan, misalnya belajar kelompok sesekali.',
                    'Berikan reward kecil untuk dirimu sendiri setelah menyelesaikan target belajar.',
                ],
            ],
            'baik' => [
                'description' => 'Selamat! Kemampuan kamu untuk melaksanakan rencana belajarmu sudah baik. Performa belajar dan konsentrasimu sudah didukung oleh kemampuan mengontrol diri sendiri, misalnya dengan mengetahui bagaimana mengecek pemahamanmu terhadap sebuah materi, membuat alat bantu (seperti rangkuman dan mindmap) untuk membantu memahami materi, mengatur waktu, dan sebagainya. Selain itu, kamu juga sudah memiliki kesadaran diri untuk mengobservasi dirimu sendiri, misalnya memonitor apakah sebuah metode belajar dapat membantumu mendapatkan hasil yang diinginkan dan membuat daftar apa saja yang masih perlu kamu tingkatkan.',
                'tips' => [
                    'Tetap jaga kedisiplinan diri untuk mengikuti jadwal dan strategi belajar yang kamu buat',
                    'Agar lebih mudah paham, pecah setiap konsep materi dalam langkah-langkah atau struktur tertentu dengan lebih sistematis',
                    'Perhatikan cara apa yang paling membantumu belajar dan pertahankan cara tersebut. Apakah dengan memberikan hadiah untuk diri sendiri, belajar dengan teman, dan sebagainya',
                    'Buat daftar konsep materi yang belum kamu kuasai supaya belajarmu lebih terarah dan perkembanganmu bisa terpantau dengan baik',
                ],
            ],
        ],

        3 => [ // Refleksi Belajar
            'kurang' => [
                'description' => 'Sepertinya kemampuan kamu untuk mengevaluasi dan memaknai proses belajarmu masih perlu ditingkatkan, nih. Kamu masih perlu meningkatkan kemampuan evaluasi diri kamu, misalnya dengan memonitor hasil belajarmu secara berkala, membandingkan apakah hasilnya sudah sesuai dengan target yang kamu inginkan, serta mengidentifikasi faktor-faktor yang memengaruhi hasil belajarmu. Selain itu, kamu juga perlu meningkatkan kemampuan untuk menindaklanjuti hasil belajarmu, misalnya dengan membuat penyesuaian akan strategi belajar jika hasilnya belum optimal.',
                'tips' => [
                    'Tentukan target nilai yang ingin kamu capai. Target bisa berupa kenaikan skor dari nilai terakhirmu, Kriteria Ketuntasan Minimum (KKM) dari guru, atau nilai rata-rata kelas.',
                    'Perhatikan perkembangan nilaimu berdasarkan target yang telah kamu tentukan. Jika ada hasil belajar yang belum optimal, coba identifikasi apa penyebabnya.',
                    'Setelah belajar atau mengerjakan tugas, catat konsep yang belum kamu mengerti untuk kamu pelajari lebih dalam.',
                    'Perhatikan hal-hal kecil di sekitarmu. Kondisi apa atau alat bantu apa yang membuat kamu lebih semangat belajar? Gunakan hal tersebut untuk meningkatkan semangatmu.',
                    'Jangan berkecil hati jika nilaimu belum memuaskan. Jadikan itu sebagai kesempatan untuk berkembang.',
                    'Ganti mindset belajarmu, jangan berorientasi pada nilai semata, melainkan pada peningkatan kemampuanmu. Hal ini akan menumbuhkan motivasi dari dalam diri.',
                ],
            ],
            'cukup' => [
                'description' => 'Kemampuan kamu untuk mengevaluasi dan memaknai proses belajarmu sudah cukup baik. Kamu sudah mulai terbiasa memantau hasil belajarmu, hanya saja masih perlu lebih konsisten dalam menindaklanjuti hasil evaluasi tersebut agar strategi belajarmu semakin optimal.',
                'tips' => [
                    'Rutin memonitor perkembangan nilaimu dari waktu ke waktu, jangan hanya sesekali.',
                    'Bandingkan hasil belajarmu dengan target yang sudah kamu tentukan sebelumnya.',
                    'Jika hasil belum sesuai target, coba cari tahu penyebabnya dan lakukan penyesuaian strategi belajar.',
                    'Rayakan pencapaianmu sekecil apa pun, ini penting untuk menjaga motivasi belajarmu.',
                ],
            ],
            'baik' => [
                'description' => 'Selamat! Kemampuan kamu untuk mengevaluasi dan memaknai proses belajarmu sudah sangat baik. Kamu terbiasa memantau hasil belajar, mengidentifikasi penyebab hasil yang kurang optimal, serta menindaklanjutinya dengan strategi belajar yang lebih baik. Pertahankan kebiasaan reflektif ini, ya.',
                'tips' => [
                    'Terus konsisten memonitor dan mengevaluasi hasil belajarmu secara berkala.',
                    'Dokumentasikan pelajaran yang kamu dapat dari setiap evaluasi agar bisa jadi referensi ke depannya.',
                    'Bantu temanmu untuk belajar melakukan evaluasi diri seperti yang sudah kamu lakukan.',
                    'Tetap terbuka pada perubahan strategi belajar meskipun hasil belajarmu sudah baik.',
                ],
            ],
        ],
    ],
];
