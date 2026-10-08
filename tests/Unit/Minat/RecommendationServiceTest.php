<?php

namespace Tests\Unit\Minat;

use App\Models\CareerField;
use App\Models\InterestCategory;
use App\Models\ProgramStudi;
use App\Services\Minat\InterestResult;
use App\Services\Minat\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecommendationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RecommendationService;
    }

    public function test_bobot_top_tiga_dan_label_kecocokan(): void
    {
        [$r, $i, $a] = $this->buatTigaKategori();

        // relevansi 3 × 1.0 = 3 → Sangat Cocok
        $prodiSangat = $this->buatProdi('Teknik Informatika', verified: true, active: true);
        $prodiSangat->interestCategories()->attach($r->id, ['relevansi' => 3]);

        // relevansi 2 × 1.0 = 2 → Cocok
        $prodiCocok = $this->buatProdi('Sistem Informasi', verified: true, active: true);
        $prodiCocok->interestCategories()->attach($r->id, ['relevansi' => 2]);

        // top2 weight: 3 × 0.6 = 1.8 → Cocok; plus top1 1×1.0 = 2.8 masih Cocok
        $prodiMix = $this->buatProdi('Multimedia', verified: true, active: true);
        $prodiMix->interestCategories()->attach([
            $r->id => ['relevansi' => 1],
            $i->id => ['relevansi' => 3],
        ]);

        $result = $this->buatInterestResult([$r, $i, $a]);
        $output = $this->service->forSma($result);

        $this->assertNull($output['empty_message']);
        $this->assertCount(3, $output['items']);

        $byName = collect($output['items'])->keyBy('nama');

        $this->assertSame(3.0, $byName['Teknik Informatika']['score']);
        $this->assertSame('Sangat Cocok', $byName['Teknik Informatika']['match_label']);

        $this->assertSame(2.0, $byName['Sistem Informasi']['score']);
        $this->assertSame('Cocok', $byName['Sistem Informasi']['match_label']);

        // 1*1.0 + 3*0.6 = 2.8
        $this->assertSame(2.8, $byName['Multimedia']['score']);
        $this->assertSame('Cocok', $byName['Multimedia']['match_label']);

        // Urut skor menurun
        $this->assertSame('Teknik Informatika', $output['items'][0]['nama']);
        $this->assertNotEmpty($byName['Teknik Informatika']['kode_tag']);
    }

    public function test_sma_hanya_prodi_verified_dan_active(): void
    {
        [$r, $i, $a] = $this->buatTigaKategori();

        $ok = $this->buatProdi('Prodi OK', verified: true, active: true);
        $ok->interestCategories()->attach($r->id, ['relevansi' => 3]);

        $unverified = $this->buatProdi('Prodi Belum Verified', verified: false, active: true);
        $unverified->interestCategories()->attach($r->id, ['relevansi' => 3]);

        $inactive = $this->buatProdi('Prodi Nonaktif', verified: true, active: false);
        $inactive->interestCategories()->attach($r->id, ['relevansi' => 3]);

        $output = $this->service->forSma($this->buatInterestResult([$r, $i, $a]));

        $this->assertCount(1, $output['items']);
        $this->assertSame('Prodi OK', $output['items'][0]['nama']);
        $this->assertNull($output['empty_message']);
    }

    public function test_sma_empty_message_bila_hanya_prodi_belum_diverifikasi(): void
    {
        [$r, $i, $a] = $this->buatTigaKategori();

        $unverified = $this->buatProdi('Prodi Belum Verified', verified: false, active: true);
        $unverified->interestCategories()->attach($r->id, ['relevansi' => 3]);

        $output = $this->service->forSma($this->buatInterestResult([$r, $i, $a]));

        $this->assertSame([], $output['items']);
        $this->assertSame(RecommendationService::EMPTY_MESSAGE_SMA_UNVERIFIED, $output['empty_message']);
    }

    public function test_sma_fallback_empty_message(): void
    {
        [$r, $i, $a] = $this->buatTigaKategori();

        // Prodi terkait kategori di luar top-3 tidak ikut; tidak ada prodi untuk top-3
        $other = InterestCategory::query()->create([
            'kode' => 'C',
            'nama' => 'Administrasi',
            'deskripsi' => 'Admin',
            'urutan' => 6,
            'is_active' => true,
        ]);
        $prodi = $this->buatProdi('Akuntansi', verified: true, active: true);
        $prodi->interestCategories()->attach($other->id, ['relevansi' => 3]);

        $output = $this->service->forSma($this->buatInterestResult([$r, $i, $a]));

        $this->assertSame([], $output['items']);
        $this->assertSame(RecommendationService::EMPTY_MESSAGE_SMA, $output['empty_message']);
    }

    public function test_smk_path_dengan_job_zone_dan_fallback(): void
    {
        [$r, $i, $a] = $this->buatTigaKategori();

        $field = CareerField::query()->create([
            'nama' => 'Teknologi Informasi',
            'deskripsi' => 'Bidang TI',
            'contoh_pekerjaan' => ['Programmer', 'Network Admin'],
            'job_zone' => 3,
            'is_active' => true,
        ]);
        $field->interestCategories()->attach($r->id, ['relevansi' => 3]);

        $inactive = CareerField::query()->create([
            'nama' => 'Nonaktif',
            'deskripsi' => null,
            'job_zone' => 2,
            'is_active' => false,
        ]);
        $inactive->interestCategories()->attach($r->id, ['relevansi' => 3]);

        $output = $this->service->forSmk($this->buatInterestResult([$r, $i, $a]));

        $this->assertNull($output['empty_message']);
        $this->assertCount(1, $output['items']);
        $this->assertSame('Teknologi Informasi', $output['items'][0]['nama']);
        $this->assertSame(3, $output['items'][0]['job_zone']);
        $this->assertSame('Sangat Cocok', $output['items'][0]['match_label']);
        $this->assertSame(['Programmer', 'Network Admin'], $output['items'][0]['contoh_pekerjaan']);

        $empty = $this->service->forSmk($this->buatInterestResult([
            InterestCategory::query()->create([
                'kode' => 'E',
                'nama' => 'Bisnis',
                'deskripsi' => 'Bisnis',
                'urutan' => 5,
                'is_active' => true,
            ]),
        ]));

        $this->assertSame([], $empty['items']);
        $this->assertSame(RecommendationService::EMPTY_MESSAGE_SMK, $empty['empty_message']);
    }

    /**
     * @return array{0: InterestCategory, 1: InterestCategory, 2: InterestCategory}
     */
    private function buatTigaKategori(): array
    {
        return [
            InterestCategory::query()->create([
                'kode' => 'R',
                'nama' => 'Teknik',
                'deskripsi' => 'Teknik',
                'urutan' => 1,
                'is_active' => true,
            ]),
            InterestCategory::query()->create([
                'kode' => 'I',
                'nama' => 'Sains',
                'deskripsi' => 'Sains',
                'urutan' => 2,
                'is_active' => true,
            ]),
            InterestCategory::query()->create([
                'kode' => 'A',
                'nama' => 'Kreatif',
                'deskripsi' => 'Kreatif',
                'urutan' => 3,
                'is_active' => true,
            ]),
        ];
    }

    private function buatProdi(string $nama, bool $verified, bool $active): ProgramStudi
    {
        return ProgramStudi::query()->create([
            'institusi' => 'Politeknik Caltex Riau',
            'nama' => $nama,
            'jenjang_pendidikan' => 'D3',
            'jurusan' => 'Komputer',
            'deskripsi' => "Deskripsi {$nama}",
            'is_verified' => $verified,
            'is_active' => $active,
        ]);
    }

    /**
     * @param  list<InterestCategory>  $categories  urut peringkat (top1, top2, ...)
     */
    private function buatInterestResult(array $categories): InterestResult
    {
        $ranked = [];
        foreach ($categories as $index => $category) {
            $ranked[] = [
                'id' => $category->id,
                'kode' => $category->kode,
                'nama' => $category->nama,
                'raw' => 10 - $index,
                'max' => 10,
                'persen' => 100.0 - ($index * 10),
                'deskripsi' => $category->deskripsi,
            ];
        }

        return new InterestResult(
            rankedCategories: $ranked,
            kode_minat: implode('', array_column($ranked, 'kode')),
            dominant_interest_id: $ranked[0]['id'] ?? null,
            secondary_interest_id: $ranked[1]['id'] ?? null,
            is_tied: false,
            result_label: $ranked[0]['nama'] ?? '',
            result_description: $ranked[0]['deskripsi'] ?? null,
            total_score: array_sum(array_column($ranked, 'raw')),
        );
    }
}
