<?php

namespace Tests\Unit\Phase4;

use App\Support\AngketProgress;
use App\Models\PenilaianPelayanan;
use Tests\TestCase;

class PenilaianModelTest extends TestCase
{
    public function test_rata_rata_dihitung_dengan_benar(): void
    {
        $p = new PenilaianPelayanan([
            'skor_materi' => 4,
            'skor_cara' => 4,
            'skor_manfaat' => 4,
        ]);
        $this->assertEquals(4.0, $p->rata_rata);

        $p2 = new PenilaianPelayanan([
            'skor_materi' => 5,
            'skor_cara' => 3,
            'skor_manfaat' => 4,
        ]);
        $this->assertEquals(4.0, $p2->rata_rata);
    }

    public function test_semua_nilai_predikat_benar(): void
    {
        $kasus = [
            [5, 5, 5, 'Sangat Baik'],
            [5, 4, 5, 'Sangat Baik'],
            [4, 4, 4, 'Baik'],
            [4, 3, 4, 'Baik'],
            [3, 3, 3, 'Cukup'],
            [2, 3, 3, 'Cukup'],
            [1, 2, 2, 'Kurang'],
            [1, 1, 1, 'Kurang'],
        ];

        foreach ($kasus as [$m, $c, $f, $expected]) {
            $p = new PenilaianPelayanan([
                'skor_materi' => $m,
                'skor_cara' => $c,
                'skor_manfaat' => $f,
            ]);
            $this->assertEquals(
                $expected,
                $p->predikat,
                "Predikat salah untuk skor {$m}/{$c}/{$f}: dapat '{$p->predikat}', ekspektasi '{$expected}'"
            );
        }
    }

    public function test_predikat_class_tailwind_tersedia_untuk_semua_predikat(): void
    {
        $kasus = [
            [5, 5, 5, 'bg-green-100 text-green-800'],
            [4, 4, 4, 'bg-blue-100 text-blue-800'],
            [3, 3, 3, 'bg-yellow-100 text-yellow-800'],
            [1, 1, 1, 'bg-red-100 text-red-800'],
        ];

        foreach ($kasus as [$m, $c, $f, $expectedClass]) {
            $p = new PenilaianPelayanan([
                'skor_materi' => $m,
                'skor_cara' => $c,
                'skor_manfaat' => $f,
            ]);
            $this->assertEquals($expectedClass, $p->predikat_class);
        }
    }

    public function test_predikat_angket_dihitung_benar(): void
    {
        $this->assertEquals('Lengkap', AngketProgress::predikat(10, 10));
        $this->assertEquals('Lengkap', AngketProgress::predikat(8, 10));
        $this->assertEquals('Sebagian', AngketProgress::predikat(5, 10));
        $this->assertEquals('Sebagian', AngketProgress::predikat(6, 10));
        $this->assertEquals('Belum Lengkap', AngketProgress::predikat(4, 10));
        $this->assertEquals('Belum Lengkap', AngketProgress::predikat(0, 10));
        $this->assertEquals('Belum Ada Soal', AngketProgress::predikat(0, 0));
    }
}
