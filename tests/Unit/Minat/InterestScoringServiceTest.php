<?php

namespace Tests\Unit\Minat;

use App\Models\InstrumentQuestion;
use App\Models\InterestCategory;
use App\Services\Minat\InterestScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class InterestScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private InterestScoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InterestScoringService;
    }

    public function test_persen_memperhitungkan_bobot_soal(): void
    {
        $category = $this->buatKategori('R', 'Teknik', 1);
        $question = $this->buatSoal($category, bobot: 2);

        $result = $this->service->score(collect([$question]), [
            $question->id => 3, // skor opsi 3 × bobot 2 = 6; max 4×2 = 8 → 75.0
        ]);

        $this->assertCount(1, $result->rankedCategories);
        $this->assertSame(6, $result->rankedCategories[0]['raw']);
        $this->assertSame(8, $result->rankedCategories[0]['max']);
        $this->assertSame(75.0, $result->rankedCategories[0]['persen']);
        $this->assertSame(6, $result->total_score);
        $this->assertSame('Teknik', $result->result_label);
        $this->assertSame($category->deskripsi, $result->result_description);
    }

    public function test_ranking_berdasarkan_persen_menurun(): void
    {
        $r = $this->buatKategori('R', 'Teknik', 1);
        $i = $this->buatKategori('I', 'Sains', 2);
        $qR = $this->buatSoal($r, bobot: 1);
        $qI = $this->buatSoal($i, bobot: 1);

        $result = $this->service->score(collect([$qR, $qI]), [
            $qR->id => 2, // 50%
            $qI->id => 4, // 100%
        ]);

        $this->assertSame(['I', 'R'], array_column($result->rankedCategories, 'kode'));
        $this->assertSame($i->id, $result->dominant_interest_id);
        $this->assertSame($r->id, $result->secondary_interest_id);
    }

    public function test_tie_break_raw_lebih_tinggi_lalu_urutan_lebih_kecil(): void
    {
        $r = $this->buatKategori('R', 'Teknik', urutan: 1);
        $i = $this->buatKategori('I', 'Sains', urutan: 2);

        // Sama persen 100%, tapi R punya raw lebih besar karena bobot 2
        $qR = $this->buatSoal($r, bobot: 2);
        $qI = $this->buatSoal($i, bobot: 1);

        $result = $this->service->score(collect([$qR, $qI]), [
            $qR->id => 4,
            $qI->id => 4,
        ]);

        $this->assertSame(100.0, $result->rankedCategories[0]['persen']);
        $this->assertSame(100.0, $result->rankedCategories[1]['persen']);
        $this->assertSame('R', $result->rankedCategories[0]['kode']);
        $this->assertSame(8, $result->rankedCategories[0]['raw']);
        $this->assertSame(4, $result->rankedCategories[1]['raw']);

        // Persen & raw sama → urutan lebih kecil menang
        $a = $this->buatKategori('A', 'Kreatif', urutan: 3);
        $s = $this->buatKategori('S', 'Sosial', urutan: 4);
        $qA = $this->buatSoal($a, bobot: 1);
        $qS = $this->buatSoal($s, bobot: 1);

        $result2 = $this->service->score(collect([$qA, $qS]), [
            $qA->id => 3,
            $qS->id => 3,
        ]);

        $this->assertSame(['A', 'S'], array_column($result2->rankedCategories, 'kode'));
    }

    public function test_is_tied_bila_selisih_persen_kurang_dari_setengah(): void
    {
        $r = $this->buatKategori('R', 'Teknik', 1);
        $i = $this->buatKategori('I', 'Sains', 2);
        $qR = $this->buatSoal($r);
        $qI = $this->buatSoal($i);

        // 100% vs 75% → tidak tied
        $notTied = $this->service->score(collect([$qR, $qI]), [
            $qR->id => 4,
            $qI->id => 3,
        ]);
        $this->assertFalse($notTied->is_tied);

        // 100% vs 99.6% → tied (selisih 0.4 < 0.5)
        $qRClose = InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'interest_category_id' => $r->id,
            'jenjang_target' => 'semua',
            'bobot' => 1,
            'question' => 'Close R',
            'options' => [
                ['label' => 'rendah', 'score' => 0],
                ['label' => 'tinggi', 'score' => 250],
            ],
            'is_active' => true,
        ]);
        $qIClose = InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'interest_category_id' => $i->id,
            'jenjang_target' => 'semua',
            'bobot' => 1,
            'question' => 'Close I',
            'options' => [
                ['label' => 'rendah', 'score' => 0],
                ['label' => 'hampir', 'score' => 249],
                ['label' => 'tinggi', 'score' => 250],
            ],
            'is_active' => true,
        ]);

        $tied = $this->service->score(collect([$qRClose, $qIClose]), [
            $qRClose->id => 1, // 250/250 = 100.0
            $qIClose->id => 1, // 249/250 = 99.6
        ]);
        $this->assertTrue($tied->is_tied);
        $this->assertEqualsWithDelta(
            0.4,
            abs($tied->rankedCategories[0]['persen'] - $tied->rankedCategories[1]['persen']),
            0.0001
        );
    }

    public function test_kode_minat_dari_tiga_kategori_teratas(): void
    {
        $cats = [
            $this->buatKategori('R', 'Teknik', 1),
            $this->buatKategori('I', 'Sains', 2),
            $this->buatKategori('A', 'Kreatif', 3),
            $this->buatKategori('S', 'Sosial', 4),
        ];

        $questions = collect([
            $this->buatSoal($cats[0]),
            $this->buatSoal($cats[1]),
            $this->buatSoal($cats[2]),
            $this->buatSoal($cats[3]),
        ]);

        $result = $this->service->score($questions, [
            $questions[0]->id => 4, // R 100
            $questions[1]->id => 3, // I 75
            $questions[2]->id => 2, // A 50
            $questions[3]->id => 1, // S 25
        ]);

        $this->assertSame('RIA', $result->kode_minat);
        $this->assertSame([
            ['id' => $cats[0]->id, 'kode' => 'R', 'nama' => 'Teknik', 'raw' => 4, 'max' => 4, 'persen' => 100.0],
            ['id' => $cats[1]->id, 'kode' => 'I', 'nama' => 'Sains', 'raw' => 3, 'max' => 4, 'persen' => 75.0],
            ['id' => $cats[2]->id, 'kode' => 'A', 'nama' => 'Kreatif', 'raw' => 2, 'max' => 4, 'persen' => 50.0],
            ['id' => $cats[3]->id, 'kode' => 'S', 'nama' => 'Sosial', 'raw' => 1, 'max' => 4, 'persen' => 25.0],
        ], $result->toArray());
    }

    public function test_kategori_tanpa_soal_tidak_masuk_peringkat(): void
    {
        $r = $this->buatKategori('R', 'Teknik', 1);
        $this->buatKategori('I', 'Sains', 2); // tanpa soal
        $qR = $this->buatSoal($r);

        $result = $this->service->score(collect([$qR]), [
            $qR->id => 4,
        ]);

        $this->assertCount(1, $result->rankedCategories);
        $this->assertSame(['R'], array_column($result->rankedCategories, 'kode'));
        $this->assertSame('R', $result->kode_minat);
    }

    public function test_jawaban_count_tidak_cocok_melempar_validation_exception(): void
    {
        $r = $this->buatKategori('R', 'Teknik', 1);
        $qR = $this->buatSoal($r);

        $this->expectException(ValidationException::class);

        try {
            $this->service->score(collect([$qR]), []);
        } catch (ValidationException $e) {
            $this->assertSame(
                'Jawaban tidak sesuai dengan daftar soal aktif.',
                $e->errors()['answers'][0]
            );
            throw $e;
        }
    }

    public function test_opsi_tidak_valid_abort_422(): void
    {
        $r = $this->buatKategori('R', 'Teknik', 1);
        $qR = $this->buatSoal($r);

        try {
            $this->service->score(collect([$qR]), [
                $qR->id => 99,
            ]);
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $this->assertSame('Pilihan jawaban tidak valid.', $e->getMessage());
        }
    }

    private function buatKategori(string $kode, string $nama, int $urutan): InterestCategory
    {
        return InterestCategory::query()->create([
            'kode' => $kode,
            'nama' => $nama,
            'deskripsi' => "Deskripsi {$nama}",
            'warna' => '#000000',
            'urutan' => $urutan,
            'is_active' => true,
        ]);
    }

    private function buatSoal(InterestCategory $category, int $bobot = 1): InstrumentQuestion
    {
        return InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'interest_category_id' => $category->id,
            'jenjang_target' => 'semua',
            'bobot' => $bobot,
            'question' => "Soal {$category->kode}",
            'options' => [
                ['label' => 'Sangat tidak tertarik', 'score' => 0],
                ['label' => 'Tidak tertarik', 'score' => 1],
                ['label' => 'Netral', 'score' => 2],
                ['label' => 'Tertarik', 'score' => 3],
                ['label' => 'Sangat tertarik', 'score' => 4],
            ],
            'is_active' => true,
        ]);
    }
}
