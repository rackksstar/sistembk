<?php

namespace Tests\Feature\Minat;

use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use App\Models\InterestCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class YolaInstrumentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_minat_kerja_riasec_mengarah_ke_hasil_holland(): void
    {
        $siswa = $this->buatSiswa();
        $categories = $this->buatKategoriRiasec();
        $questions = $this->buatSoalRiasec($categories);

        $answers = $questions->mapWithKeys(function (InstrumentQuestion $question) use ($categories) {
            $isDominant = $question->interest_category_id === $categories['R']->id;

            return [$question->id => $isDominant ? 4 : 1];
        })->all();

        $response = $this->actingAs($siswa)
            ->post(route('siswa.instruments.store'), [
                'category' => InstrumentQuestion::CATEGORY_MINAT_KERJA,
                'answers' => $answers,
            ])
            ->assertSessionHas('success');

        $submission = InstrumentSubmission::query()
            ->where('student_id', $siswa->id)
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_KERJA)
            ->latest('id')
            ->firstOrFail();

        $this->assertNotEmpty($submission->kode_minat);
        $this->assertStringStartsWith('R', $submission->kode_minat);
        $response->assertRedirect(route('siswa.instruments.hasil', $submission));

        $this->actingAs($siswa)
            ->get(route('siswa.instruments.hasil', $submission))
            ->assertOk()
            ->assertSee('Hasil Minat Bakat Kerja')
            ->assertSee($submission->kode_minat)
            ->assertSee('Rincian per Dimensi RIASEC')
            ->assertSee('Rekomendasi Karier');
    }

    public function test_index_minat_kerja_menampilkan_form_likert_seperti_referensi(): void
    {
        $siswa = $this->buatSiswa();
        $categories = $this->buatKategoriRiasec();
        $questions = $this->buatSoalRiasec($categories);

        $response = $this->actingAs($siswa)
            ->get(route('siswa.instruments.index', ['category' => 'minat_kerja']))
            ->assertOk()
            ->assertSee('Minat Bakat Kerja')
            ->assertSee('Talents Mapping (RIASEC)')
            ->assertSee('Kirim dan Lihat Skor')
            ->assertSee($questions->first()->question)
            ->assertSee('Sangat Tidak Suka')
            ->assertSee('Sangat Suka');

        // Form Likert ref: semua soal di halaman yang sama (bukan wizard intro).
        $response->assertDontSee('Mulai asesmen');
    }

    public function test_submit_strategi_belajar_dengan_section_menampilkan_hasil_per_bagian(): void
    {
        $siswa = $this->buatSiswa();
        $questions = collect();

        foreach ([1, 2, 3] as $section) {
            $questions = $questions->merge(
                $this->buatSoalKlasik(InstrumentQuestion::CATEGORY_GAYA_BELAJAR, 2, $section)
            );
        }

        $answers = $questions->mapWithKeys(fn (InstrumentQuestion $q) => [$q->id => 4])->all();

        $this->actingAs($siswa)
            ->post(route('siswa.instruments.store'), [
                'category' => InstrumentQuestion::CATEGORY_GAYA_BELAJAR,
                'answers' => $answers,
            ])
            ->assertRedirect(route('siswa.instruments.strategi-belajar-result'))
            ->assertSessionHas('success');

        $this->actingAs($siswa)
            ->get(route('siswa.instruments.strategi-belajar-result'))
            ->assertOk()
            ->assertSee('Hasil Tes Strategi Belajar')
            ->assertSee('Perencanaan Belajar')
            ->assertSee('Eksekusi Belajar')
            ->assertSee('Refleksi Belajar');
    }

    public function test_index_default_membuka_minat_kerja(): void
    {
        $siswa = $this->buatSiswa();
        $categories = $this->buatKategoriRiasec();
        $this->buatSoalRiasec($categories);

        $this->actingAs($siswa)
            ->get(route('siswa.instruments.index'))
            ->assertOk()
            ->assertSee('Minat Bakat Kerja')
            ->assertSee('Modul Yola');
    }

    public function test_alias_minat_bakat_diarahkan_ke_key(): void
    {
        $siswa = $this->buatSiswa();

        $this->actingAs($siswa)
            ->get(route('siswa.instruments.index', ['category' => 'minat_bakat']))
            ->assertRedirect(route('siswa.minat-bakat.index'));
    }

    private function buatSiswa(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    /**
     * @return array<string, InterestCategory>
     */
    private function buatKategoriRiasec(): array
    {
        $out = [];
        foreach (['R', 'I', 'A', 'S', 'E', 'C'] as $i => $kode) {
            $out[$kode] = InterestCategory::query()->create([
                'kode' => $kode,
                'nama' => InstrumentQuestion::RIASEC_CODES[$kode] ?? $kode,
                'deskripsi' => "Deskripsi {$kode}",
                'urutan' => $i + 1,
                'is_active' => true,
            ]);
        }

        return $out;
    }

    /**
     * @param  array<string, InterestCategory>  $categories
     * @return Collection<int, InstrumentQuestion>
     */
    private function buatSoalRiasec(array $categories): Collection
    {
        $options = InstrumentQuestion::TALENTS_LIKERT_OPTIONS;

        return collect($categories)->values()->map(function (InterestCategory $category, int $index) use ($options) {
            return InstrumentQuestion::query()->create([
                'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                'interest_category_id' => $category->id,
                'talent_code' => $category->kode,
                'question' => "Soal RIASEC {$category->kode}-{$index}-".uniqid(),
                'options' => $options,
                'is_active' => true,
                'jenjang_target' => 'semua',
                'bobot' => 1,
            ]);
        });
    }

    /**
     * @return Collection<int, InstrumentQuestion>
     */
    private function buatSoalKlasik(string $category, int $count, ?int $section = null)
    {
        $options = [
            ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
            ['label' => 'Tidak Sesuai', 'score' => 2],
            ['label' => 'Cukup Sesuai', 'score' => 3],
            ['label' => 'Sesuai', 'score' => 4],
            ['label' => 'Sangat Sesuai', 'score' => 5],
        ];

        return collect(range(1, $count))->map(fn (int $i) => InstrumentQuestion::query()->create([
            'category' => $category,
            'section' => $section,
            'question' => "Soal {$category} {$section}-{$i}-".uniqid(),
            'options' => $options,
            'is_active' => true,
            'jenjang_target' => 'semua',
            'bobot' => 1,
        ]));
    }
}
