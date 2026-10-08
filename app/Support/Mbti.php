<?php

namespace App\Support;

/**
 * Logika penskoran ala tes tipe kepribadian MBTI: 4 dimensi (EI, SN, TF, JP),
 * setiap soal berupa pilihan paksa (forced-choice) antara dua kutub yang
 * berlawanan. Kutub yang paling sering dipilih siswa pada tiap dimensi
 * menyusun 4 huruf kode kepribadian akhir (mis. "INFP").
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
     */
    public static function optionHasPole(mixed $option): bool
    {
        return is_array($option) && ! empty($option['pole']) && is_string($option['pole']);
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
