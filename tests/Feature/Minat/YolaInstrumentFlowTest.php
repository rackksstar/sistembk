<?php

namespace Tests\Feature\Minat;

use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class YolaInstrumentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_minat_kerja_mengarah_ke_halaman_hasil(): void
    {
        $siswa = $this->buatSiswa();
        $questions = $this->buatSoalKlasik(InstrumentQuestion::CATEGORY_MINAT_KERJA, 3);

        $answers = $questions->mapWithKeys(fn (InstrumentQuestion $q) => [$q->id => 4])->all();

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

        $response->assertRedirect(route('siswa.instruments.hasil', $submission));

        $this->actingAs($siswa)
            ->get(route('siswa.instruments.hasil', $submission))
            ->assertOk()
            ->assertSee($submission->result_label);
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
        $this->buatSoalKlasik(InstrumentQuestion::CATEGORY_MINAT_KERJA, 1);

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
