<?php

namespace App\Support;

/**
 * Logika penskoran ala tes tipe kepribadian MBTI (format 16Personalities):
 * 4 dimensi (EI, SN, TF, JP), setiap soal berupa SATU pernyataan yang dinilai
 * dengan skala Likert persetujuan 1-5. Jawaban "Sesuai" mendukung kutub kunci
 * soal, jawaban "Tidak Sesuai" mendukung kutub lawannya — makin ekstrem
 * pilihan (1 atau 5) makin besar bobot poinnya. Kutub per dimensi dengan poin
 * tertinggi menyusun 4 huruf kode kepribadian akhir (mis. "INFP").
 */
class Mbti
{
    public const AXES = [
        'EI' => ['E' => 'Ekstrover', 'I' => 'Introver'],
        'SN' => ['S' => 'Sensing', 'N' => 'Intuisi'],
        'TF' => ['T' => 'Thinking', 'F' => 'Feeling'],
        'JP' => ['J' => 'Judging', 'P' => 'Perceiving'],
    ];

    public const TYPES = [
        'ISTJ' => ['name' => 'Sang Logistikawan', 'description' => 'Praktis, teliti, dan bertanggung jawab. Menyukai keteraturan, aturan yang jelas, dan menyelesaikan tugas tepat waktu berdasarkan fakta dan pengalaman nyata.'],
        'ISFJ' => ['name' => 'Sang Pelindung', 'description' => 'Hangat, teliti, dan setia. Senang membantu orang lain secara diam-diam, menghargai tradisi, dan bekerja keras memastikan kebutuhan orang di sekitarnya terpenuhi.'],
        'INFJ' => ['name' => 'Sang Penasihat', 'description' => 'Idealis, penuh wawasan, dan berprinsip kuat. Peka terhadap perasaan orang lain serta punya visi jangka panjang untuk membuat perubahan yang bermakna.'],
        'INTJ' => ['name' => 'Sang Arsitek', 'description' => 'Strategis, mandiri, dan analitis. Suka merancang rencana jangka panjang, bekerja dengan konsep besar, dan menetapkan standar tinggi bagi diri sendiri.'],
        'ISTP' => ['name' => 'Sang Virtuoso', 'description' => 'Logis, tenang, dan suka mencoba hal secara praktik langsung. Cepat memahami cara kerja sesuatu dan tangkas mencari solusi saat menghadapi masalah teknis.'],
        'ISFP' => ['name' => 'Sang Petualang', 'description' => 'Fleksibel, peka secara estetika, dan mengikuti kata hati. Menghargai kebebasan berekspresi serta nyaman menjalani hidup sesuai nilai pribadinya.'],
        'INFP' => ['name' => 'Sang Mediator', 'description' => 'Idealis, empatik, dan setia pada nilai-nilai pribadinya. Kreatif dalam mengekspresikan diri serta peduli pada makna dan kebaikan di balik setiap tindakan.'],
        'INTP' => ['name' => 'Sang Pemikir', 'description' => 'Ingin tahu, logis, dan suka mengeksplorasi ide. Senang menganalisis konsep abstrak serta mencari pemahaman mendalam sebelum mengambil kesimpulan.'],
        'ESTP' => ['name' => 'Sang Pengusaha', 'description' => 'Enerjik, spontan, dan berani mengambil risiko. Cepat bertindak menghadapi situasi nyata dan senang belajar sambil langsung mempraktikkannya.'],
        'ESFP' => ['name' => 'Sang Penghibur', 'description' => 'Ceria, hangat, dan spontan. Senang menjadi pusat perhatian yang positif, mudah bergaul, dan pandai mencairkan suasana di sekitarnya.'],
        'ENFP' => ['name' => 'Sang Kampanyer', 'description' => 'Antusias, kreatif, dan penuh semangat sosial. Mudah melihat kemungkinan baru serta pandai menginspirasi dan menyemangati orang lain.'],
        'ENTP' => ['name' => 'Sang Pendebat', 'description' => 'Cerdas, penuh ide, dan senang berdiskusi. Suka menantang cara berpikir lama dan cepat melihat peluang atau sudut pandang baru.'],
        'ESTJ' => ['name' => 'Sang Eksekutif', 'description' => 'Tegas, terorganisir, dan berorientasi hasil. Terampil mengatur orang dan sumber daya agar tugas selesai secara efisien sesuai rencana.'],
        'ESFJ' => ['name' => 'Sang Konsul', 'description' => 'Ramah, kooperatif, dan peduli pada keharmonisan kelompok. Senang membantu orang lain merasa didukung dan dihargai dalam kehidupan sehari-hari.'],
        'ENFJ' => ['name' => 'Sang Protagonis', 'description' => 'Karismatik, peduli, dan mudah memotivasi orang lain. Punya kepekaan sosial tinggi serta senang membantu orang di sekitarnya bertumbuh.'],
        'ENTJ' => ['name' => 'Sang Komandan', 'description' => 'Percaya diri, tegas, dan berorientasi pada tujuan besar. Terampil memimpin, mengambil keputusan cepat, dan menggerakkan orang lain mencapai target.'],
    ];

    /**
     * Menentukan dimensi (EI/SN/TF/JP) tempat sebuah huruf kutub berada.
     */
    public static function axisForPole(string $pole): ?string
    {
        foreach (self::AXES as $axis => $poles) {
            if (array_key_exists($pole, $poles)) {
                return $axis;
            }
        }

        return null;
    }

    /**
     * Apakah sebuah opsi jawaban memakai format kutub MBTI (punya 'pole').
     * Dipakai untuk mendeteksi soal format lama (pilihan paksa dua kutub).
     */
    public static function optionHasPole(mixed $option): bool
    {
        return is_array($option) && ! empty($option['pole']) && is_string($option['pole']);
    }

    /**
     * Penskoran Likert ala 16Personalities.
     *
     * @param  list<array{pole: string, score: int}>  $ballots  Kutub kunci tiap soal + skor Likert 1-5.
     * @return array{code: string, type: array{name: string, description: string}, confidence: float, tally: array<string, int>}
     */
    public static function scoreLikert(array $ballots): array
    {
        $points = [];

        foreach ($ballots as $ballot) {
            $pole = (string) ($ballot['pole'] ?? '');
            $score = (int) ($ballot['score'] ?? 3);
            $axis = self::axisForPole($pole);

            if ($axis === null || $score < 1 || $score > 5 || $score === 3) {
                continue;
            }

            $opposite = null;
            foreach (array_keys(self::AXES[$axis]) as $letter) {
                if ($letter !== $pole) {
                    $opposite = $letter;
                    break;
                }
            }

            if ($score > 3) {
                $points[$pole] = ($points[$pole] ?? 0) + ($score - 3);
            } elseif ($opposite !== null) {
                $points[$opposite] = ($points[$opposite] ?? 0) + (3 - $score);
            }
        }

        return self::scoreFromPoints($points);
    }

    /**
     * Menyusun kode 4 huruf dari poin per kutub (dipakai skoring Likert).
     *
     * @param  array<string, int>  $points
     * @return array{code: string, type: array{name: string, description: string}, confidence: float, tally: array<string, int>}
     */
    public static function scoreFromPoints(array $points): array
    {
        $code = '';
        $confidenceSum = 0.0;
        $axesCounted = 0;

        foreach (self::AXES as $letters) {
            [$first, $second] = array_keys($letters);
            $countFirst = (int) ($points[$first] ?? 0);
            $countSecond = (int) ($points[$second] ?? 0);
            $total = $countFirst + $countSecond;

            // Jika seri, defaultkan ke kutub pertama dimensi (E, S, T, J).
            $code .= $countSecond > $countFirst ? $second : $first;

            if ($total > 0) {
                $confidenceSum += (max($countFirst, $countSecond) / $total) * 100;
                $axesCounted++;
            }
        }

        $confidence = $axesCounted > 0 ? round($confidenceSum / $axesCounted, 2) : 0.0;

        return [
            'code' => $code,
            'type' => self::TYPES[$code] ?? ['name' => $code, 'description' => "Tipe kepribadian {$code}."],
            'confidence' => $confidence,
            'tally' => $points,
        ];
    }

    /**
     * @param  array<int, string>  $poles  Huruf kutub (E/I/S/N/T/F/J/P) dari setiap jawaban siswa.
     * @return array{code: string, type: array{name: string, description: string}, confidence: float, tally: array<string, int>}
     */
    public static function score(array $poles): array
    {
        $tally = [];

        foreach ($poles as $pole) {
            $tally[$pole] = ($tally[$pole] ?? 0) + 1;
        }

        $code = '';
        $confidenceSum = 0.0;
        $axesCounted = 0;

        foreach (self::AXES as $letters) {
            [$first, $second] = array_keys($letters);
            $countFirst = $tally[$first] ?? 0;
            $countSecond = $tally[$second] ?? 0;
            $total = $countFirst + $countSecond;

            // Jika seri, defaultkan ke kutub pertama dimensi (E, S, T, J).
            $code .= $countSecond > $countFirst ? $second : $first;

            if ($total > 0) {
                $confidenceSum += (max($countFirst, $countSecond) / $total) * 100;
                $axesCounted++;
            }
        }

        $confidence = $axesCounted > 0 ? round($confidenceSum / $axesCounted, 2) : 0.0;

        return [
            'code' => $code,
            'type' => self::TYPES[$code] ?? ['name' => $code, 'description' => "Tipe kepribadian {$code}."],
            'confidence' => $confidence,
            'tally' => $tally,
        ];
    }

    /**
     * Kelompok peran ala 16Personalities berdasarkan dua huruf tengah kode.
     */
    public const ROLES = [
        'NT' => [
            'name' => 'Analis',
            'description' => 'Pemikir konseptual yang mengandalkan logika, strategi, dan rasa ingin tahu untuk memecahkan masalah.',
        ],
        'NF' => [
            'name' => 'Diplomat',
            'description' => 'Idealis yang empatik — peka terhadap perasaan orang lain dan pandai menginspirasi perubahan baik.',
        ],
        'SJ' => [
            'name' => 'Penjaga',
            'description' => 'Pelaksana yang teliti, setia, dan dapat diandalkan — menjaga keteraturan dan tanggung jawab.',
        ],
        'SP' => [
            'name' => 'Penjelajah',
            'description' => 'Praktisi spontan yang luwes dan adaptif — belajar paling cepat lewat pengalaman langsung.',
        ],
    ];

    /**
     * Kekuatan, tantangan, dan tips belajar per tipe (ringkas, untuk siswa).
     *
     * @var array<string, array{strengths: list<string>, challenges: list<string>, study_tips: list<string>}>
     */
    public const DETAILS = [
        'ISTJ' => [
            'strengths' => ['Jujur dan memegang komitmen sampai tuntas.', 'Teliti dan rapi dalam mengerjakan tugas.', 'Dapat diandalkan oleh teman dan guru.'],
            'challenges' => ['Kurang nyaman dengan perubahan mendadak.', 'Cenderung kaku bila rencana tidak berjalan.'],
            'study_tips' => ['Susun jadwal belajar rutin dan patuhi target harian.', 'Siapkan rencana cadangan saat jadwal berubah.'],
        ],
        'ISFJ' => [
            'strengths' => ['Tekun dan sabar menyelesaikan pekerjaan detail.', 'Peduli dan siap membantu teman yang kesulitan.', 'Menghormati aturan dan tata tertib.'],
            'challenges' => ['Sungkan menolak permintaan orang lain.', 'Menghindari konflik meski pendapatnya benar.'],
            'study_tips' => ['Belajar kelompok kecil agar berani berpendapat.', 'Latih berkata tidak pada distraksi saat belajar.'],
        ],
        'INFJ' => [
            'strengths' => ['Punya visi dan tujuan belajar yang jelas.', 'Peka memahami perasaan teman.', 'Berprinsip dan konsisten pada nilai yang diyakini.'],
            'challenges' => ['Mudah lelah bila terlalu banyak bersosialisasi.', 'Perfeksionis terhadap standar diri sendiri.'],
            'study_tips' => ['Bagi target besar menjadi langkah kecil yang realistis.', 'Sediakan waktu istirahat menyendiri di antara aktivitas.'],
        ],
        'INTJ' => [
            'strengths' => ['Strategis dan pandai merancang rencana jangka panjang.', 'Mandiri dan tidak mudah ikut-ikutan.', 'Analitis dalam menilai informasi.'],
            'challenges' => ['Tidak sabar pada proses yang lambat.', 'Terlihat dingin saat menyampaikan kritik.'],
            'study_tips' => ['Gunakan peta konsep untuk materi yang kompleks.', 'Diskusikan idemu agar terasah sudut pandang lain.'],
        ],
        'ISTP' => [
            'strengths' => ['Tenang dan logis saat menghadapi masalah.', 'Cepat memahami cara kerja sesuatu lewat praktik.', 'Fleksibel dan mudah beradaptasi.'],
            'challenges' => ['Cepat bosan pada teori yang panjang.', 'Menunda tugas yang dianggap tidak penting.'],
            'study_tips' => ['Variasikan belajar dengan praktik langsung atau eksperimen.', 'Pasang pengingat tenggat agar tidak menunda.'],
        ],
        'ISFP' => [
            'strengths' => ['Peka terhadap keindahan dan detail.', 'Ramah dan tidak suka konflik.', 'Setia pada nilai dan perasaan pribadi.'],
            'challenges' => ['Sulit menerima kritik meski membangun.', 'Enggan merencanakan jauh ke depan.'],
            'study_tips' => ['Gunakan catatan visual dan warna agar materi mudah diingat.', 'Minta umpan balik guru secara berkala.'],
        ],
        'INFP' => [
            'strengths' => ['Kreatif dan imajinatif dalam mengekspresikan ide.', 'Empatik dan tulus peduli pada orang lain.', 'Setia pada makna dan nilai pribadi.'],
            'challenges' => ['Mudah tenggelam dalam perasaan sendiri.', 'Sulit fokus saat topik terasa tidak bermakna.'],
            'study_tips' => ['Hubungkan materi dengan tujuan atau makna pribadimu.', 'Tulis jurnal singkat untuk mengurai pikiran.'],
        ],
        'INTP' => [
            'strengths' => ['Rasa ingin tahu yang besar terhadap cara kerja sesuatu.', 'Logis dan objektif dalam menganalisis.', 'Senang mengeksplorasi ide-ide baru.'],
            'challenges' => ['Sering menunda eksekusi karena terlalu banyak berpikir.', 'Kurang tertarik pada rutinitas dan detail administratif.'],
            'study_tips' => ['Tetapkan batas waktu berpikir lalu segera kerjakan.', 'Gunakan daftar periksa agar detail tidak terlewat.'],
        ],
        'ESTP' => [
            'strengths' => ['Berani mencoba dan cepat mengambil tindakan.', 'Tangkas menyelesaikan masalah yang mendesak.', 'Mudah bergaul dan luwes di lingkungan baru.'],
            'challenges' => ['Kurang sabar pada teori dan perencanaan panjang.', 'Bertindak impulsif tanpa memikirkan risiko.'],
            'study_tips' => ['Belajar lewat kuis, permainan peran, dan praktik.', 'Luangkan 5 menit merencana sebelum mengerjakan soal.'],
        ],
        'ESFP' => [
            'strengths' => ['Ceria dan pandai mencairkan suasana.', 'Mudah berteman dan bekerja dalam kelompok.', 'Berani tampil dan berekspresi.'],
            'challenges' => ['Mudah terdistraksi hal yang menyenangkan.', 'Menghindari topik yang membosankan.'],
            'study_tips' => ['Belajar berkelompok sambil saling menguji hafalan.', 'Gunakan timer fokus singkat diselingi jeda.'],
        ],
        'ENFP' => [
            'strengths' => ['Antusias dan pandai memotivasi teman.', 'Kreatif melihat banyak kemungkinan.', 'Hangat dan mudah membangun relasi.'],
            'challenges' => ['Sering memulai banyak hal tapi sulit menyelesaikan.', 'Mudah kehilangan fokus pada detail.'],
            'study_tips' => ['Pilih satu target per sesi agar selesai tuntas.', 'Tulis ide-ide dulu baru eksekusi satu per satu.'],
        ],
        'ENTP' => [
            'strengths' => ['Cerdas berargumen dan cepat melihat peluang.', 'Senang berdiskusi dan bertukar ide.', 'Inventif mencari cara baru yang lebih efisien.'],
            'challenges' => ['Cepat bosan pada rutinitas dan aturan.', 'Sering berdebat sampai lupa tujuan diskusi.'],
            'study_tips' => ['Ikut debat atau diskusi untuk memperdalam materi.', 'Buat rutinitas belajar yang fleksibel tapi konsisten.'],
        ],
        'ESTJ' => [
            'strengths' => ['Tegas dan pandai mengatur pekerjaan kelompok.', 'Disiplin dan berorientasi hasil.', 'Jujur dan berani menyampaikan pendapat.'],
            'challenges' => ['Kurang sabar pada pendapat yang berbeda.', 'Terlalu fokus hasil sampai lupa proses.'],
            'study_tips' => ['Pimpin kelompok belajar agar semua terbantu.', 'Dengarkan dulu penjelasan guru sampai tuntas.'],
        ],
        'ESFJ' => [
            'strengths' => ['Kooperatif dan peduli keharmonisan kelompok.', 'Rajin membantu teman yang kesulitan belajar.', 'Teratur dan bertanggung jawab.'],
            'challenges' => ['Terlalu memikirkan penilaian orang lain.', 'Sulit berkata tidak pada ajakan bersosialisasi.'],
            'study_tips' => ['Jadwalkan waktu belajar dan waktu bersosialisasi terpisah.', 'Minta teman menguji pemahamanmu sebelum ujian.'],
        ],
        'ENFJ' => [
            'strengths' => ['Karismatik dan pandai memotivasi orang lain.', 'Peka terhadap kebutuhan teman.', 'Terorganisir dalam menggerakkan kelompok.'],
            'challenges' => ['Terlalu mengutamakan orang lain sampai lupa diri.', 'Kecewa berat saat usahanya tidak dihargai.'],
            'study_tips' => ['Mengajari teman adalah cara terbaik memperdalam materimu.', 'Sisihkan waktu istirahat untuk dirimu sendiri.'],
        ],
        'ENTJ' => [
            'strengths' => ['Percaya diri memimpin dan mengambil keputusan.', 'Berorientasi target dan efisien.', 'Berani menghadapi tantangan besar.'],
            'challenges' => ['Tidak sabar pada anggota yang lambat.', 'Terlihat dominan dalam kerja kelompok.'],
            'study_tips' => ['Bagi tugas kelompok sesuai kekuatan tiap anggota.', 'Tetapkan target belajar mingguan yang terukur.'],
        ],
    ];

    /**
     * Kelompok peran untuk sebuah kode tipe (mis. "INFP" → Diplomat/NF).
     *
     * @return array{name: string, description: string}
     */
    public static function roleFor(string $code): array
    {
        // Kelompok ala 16Personalities: Analis (NT), Diplomat (NF),
        // Penjaga (SJ), Penjelajah (SP).
        $code = strtoupper($code);

        $key = match (true) {
            in_array($code, ['INTJ', 'INTP', 'ENTJ', 'ENTP'], true) => 'NT',
            in_array($code, ['INFJ', 'INFP', 'ENFJ', 'ENFP'], true) => 'NF',
            in_array($code, ['ISTJ', 'ISFJ', 'ESTJ', 'ESFJ'], true) => 'SJ',
            in_array($code, ['ISTP', 'ISFP', 'ESTP', 'ESFP'], true) => 'SP',
            default => null,
        };

        return ($key !== null && isset(self::ROLES[$key]))
            ? self::ROLES[$key]
            : ['name' => '-', 'description' => 'Kelompok peran belum diketahui.'];
    }

    /**
     * Rincian lengkap sebuah tipe: nama, deskripsi, peran, kekuatan,
     * tantangan, dan tips belajar.
     *
     * @return array{name: string, description: string, role: array{name: string, description: string}, strengths: list<string>, challenges: list<string>, study_tips: list<string>}
     */
    public static function detailFor(string $code): array
    {
        $code = strtoupper($code);
        $type = self::TYPES[$code] ?? ['name' => $code, 'description' => "Tipe kepribadian {$code}."];
        $detail = self::DETAILS[$code] ?? ['strengths' => [], 'challenges' => [], 'study_tips' => []];

        return [
            'name' => $type['name'],
            'description' => $type['description'],
            'role' => self::roleFor($code),
            'strengths' => $detail['strengths'],
            'challenges' => $detail['challenges'],
            'study_tips' => $detail['study_tips'],
        ];
    }

    /**
     * Rincian per dimensi untuk ditampilkan di halaman hasil:
     * berapa kali tiap kutub dipilih dan mana yang menang.
     *
     * @param  array<string, int>  $tally
     * @return list<array{axis: string, code: string, chosen: string, chosen_label: string, other: string, other_label: string, count: int, total: int, percentage: int}>
     */
    public static function axisBreakdown(array $tally): array
    {
        $breakdown = [];

        foreach (self::AXES as $axis => $letters) {
            [$first, $second] = array_keys($letters);
            [$firstName, $secondName] = array_values($letters);
            $countFirst = (int) ($tally[$first] ?? 0);
            $countSecond = (int) ($tally[$second] ?? 0);
            $total = $countFirst + $countSecond;

            if ($total === 0) {
                continue;
            }

            $winnerSecond = $countSecond > $countFirst;
            $chosen = $winnerSecond ? $second : $first;
            $breakdown[] = [
                'axis' => $axis,
                'code' => $chosen,
                'chosen_label' => $winnerSecond ? $secondName : $firstName,
                'other' => $winnerSecond ? $first : $second,
                'other_label' => $winnerSecond ? $firstName : $secondName,
                'count' => $winnerSecond ? $countSecond : $countFirst,
                'total' => $total,
                'percentage' => (int) round((max($countFirst, $countSecond) / $total) * 100),
            ];
        }

        return $breakdown;
    }
}
