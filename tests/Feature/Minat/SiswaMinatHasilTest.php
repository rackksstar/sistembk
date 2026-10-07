<?php

namespace Tests\Feature\Minat;

use App\Models\CareerField;
use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use App\Models\InterestCategory;
use App\Models\Kelas;
use App\Models\ProgramStudi;
use App\Models\Sekolah;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SiswaMinatHasilTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_sma_melihat_prodi_terverifikasi_di_hasil(): void
    {
        $this->skipUnlessHasilRoutesReady();

        [$siswa] = $this->buatSiswaDenganJenjang('SMA');
        $categories = $this->buatKategoriRiasec();
        $this->buatSoalMinat($categories);
        $this->buatProdiTerverifikasi($categories['I']);

        $answers = $this->jawabSemuaSoalDominan($categories['I']->id);
        $this->actingAs($siswa)
            ->post(route('siswa.instruments.store'), [
                'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                'answers' => $answers,
            ])
            ->assertRedirect();

        $submission = InstrumentSubmission::query()
            ->where('student_id', $siswa->id)
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->latest('id')
            ->firstOrFail();

        $this->assertGreaterThan(0, (float) collect($submission->category_scores)->max('persen'));
        $this->assertSame(100.0, (float) collect($submission->category_scores)
            ->firstWhere('id', $categories['I']->id)['persen']);

        $this->actingAs($siswa)
            ->get(route('siswa.instruments.hasil', $submission))
            ->assertOk()
            ->assertSee('Kode Minat')
            ->assertSee('100.0%')
            ->assertSee('Teknik Informatika')
            ->assertSee('Politeknik Caltex Riau')
            ->assertDontSee('Hasil belum bisa dibaca sebagai minat dominan');
    }

    public function test_hasil_inconclusive_bila_semua_skor_nol(): void
    {
        $this->skipUnlessHasilRoutesReady();

        [$siswa] = $this->buatSiswaDenganJenjang('SMA');
        $categories = $this->buatKategoriRiasec();
        $this->buatSoalMinat($categories);

        $answers = InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->where('is_active', true)
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [$id => 0])
            ->all();

        $this->actingAs($siswa)
            ->post(route('siswa.instruments.store'), [
                'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                'answers' => $answers,
            ])
            ->assertRedirect();

        $submission = InstrumentSubmission::query()
            ->where('student_id', $siswa->id)
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(0, (int) $submission->total_score);

        $this->actingAs($siswa)
            ->get(route('siswa.instruments.hasil', $submission))
            ->assertOk()
            ->assertSee('Hasil belum bisa dibaca sebagai minat dominan')
            ->assertSee('Hasil skor belum cukup untuk rekomendasi')
            ->assertSee('0.0%')
            ->assertDontSee('Teknik Informatika');
    }

    public function test_siswa_smk_melihat_karier_dan_job_zone(): void
    {
        $this->skipUnlessHasilRoutesReady();

        [$siswa] = $this->buatSiswaDenganJenjang('SMK');
        $categories = $this->buatKategoriRiasec();
        $this->buatSoalMinat($categories);
        $this->buatBidangKarier($categories['I']);

        $answers = $this->jawabSemuaSoalDominan($categories['I']->id);
        $this->actingAs($siswa)
            ->post(route('siswa.instruments.store'), [
                'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                'answers' => $answers,
            ])
            ->assertRedirect();

        $submission = InstrumentSubmission::query()
            ->where('student_id', $siswa->id)
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->latest('id')
            ->firstOrFail();

        $this->actingAs($siswa)
            ->get(route('siswa.instruments.hasil', $submission))
            ->assertOk()
            ->assertSee('Pengembang Web')
            ->assertSee('Job Zone');
    }

    public function test_siswa_lain_403_pada_hasil(): void
    {
        $this->skipUnlessHasilRoutesReady();

        [$pemilik] = $this->buatSiswaDenganJenjang('SMA');
        [$orangLain] = $this->buatSiswaDenganJenjang('SMA');

        $submission = InstrumentSubmission::query()->create([
            'student_id' => $pemilik->id,
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'jenjang' => 'SMA',
            'kode_minat' => 'IRC',
            'category_scores' => [],
            'total_score' => 10,
            'result_label' => 'Teknologi & Sains',
            'result_description' => 'Deskripsi',
            'submitted_at' => now(),
        ]);

        $this->actingAs($orangLain)
            ->get(route('siswa.instruments.hasil', $submission))
            ->assertForbidden();
    }

    public function test_pdf_hasil_dapat_diunduh_oleh_pemilik(): void
    {
        $this->skipUnlessHasilRoutesReady();

        [$siswa] = $this->buatSiswaDenganJenjang('SMA');
        $categories = $this->buatKategoriRiasec();

        $submission = InstrumentSubmission::query()->create([
            'student_id' => $siswa->id,
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'jenjang' => 'SMA',
            'kode_minat' => 'IRC',
            'category_scores' => [
                [
                    'id' => $categories['I']->id,
                    'kode' => 'I',
                    'nama' => 'Teknologi & Sains',
                    'raw' => 20,
                    'max' => 20,
                    'persen' => 100.0,
                ],
            ],
            'dominant_interest_id' => $categories['I']->id,
            'secondary_interest_id' => $categories['R']->id,
            'total_score' => 20,
            'result_label' => 'Teknologi & Sains',
            'result_description' => $categories['I']->deskripsi,
            'submitted_at' => now(),
        ]);

        $this->actingAs($siswa)
            ->get(route('siswa.instruments.hasil.pdf', $submission))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    private function skipUnlessHasilRoutesReady(): void
    {
        if (! Route::has('siswa.instruments.hasil') || ! Route::has('siswa.instruments.hasil.pdf')) {
            $this->markTestSkipped('Phase 5 routes siswa.instruments.hasil belum tersedia.');
        }
    }

    /**
     * @return array{0: User, 1: Student, 2: Kelas}
     */
    private function buatSiswaDenganJenjang(string $jenjang): array
    {
        $suffix = fake()->unique()->numerify('####');

        $sekolah = Sekolah::query()->create([
            'nama' => "Sekolah {$jenjang} Test {$suffix}",
            'is_mou' => true,
            'is_active' => true,
        ]);

        $kelas = Kelas::query()->create([
            'sekolah_id' => $sekolah->id,
            'nama' => "XII {$jenjang} {$suffix}",
            'jenjang' => $jenjang,
            'tingkatan' => 'XII',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $student = Student::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2008-01-01',
            'kelas_id' => $kelas->id,
            'school' => $sekolah->nama,
        ]);

        return [$user, $student, $kelas];
    }

    /**
     * @return array<string, InterestCategory>
     */
    private function buatKategoriRiasec(): array
    {
        $defs = [
            'R' => ['nama' => 'Teknik', 'urutan' => 1],
            'I' => ['nama' => 'Teknologi & Sains', 'urutan' => 2],
            'A' => ['nama' => 'Kreatif', 'urutan' => 3],
            'S' => ['nama' => 'Sosial', 'urutan' => 4],
            'E' => ['nama' => 'Bisnis', 'urutan' => 5],
            'C' => ['nama' => 'Administrasi & Data', 'urutan' => 6],
        ];

        $out = [];
        foreach ($defs as $kode => $def) {
            $out[$kode] = InterestCategory::query()->create([
                'kode' => $kode,
                'nama' => $def['nama'],
                'deskripsi' => "Deskripsi {$def['nama']}",
                'urutan' => $def['urutan'],
                'is_active' => true,
            ]);
        }

        return $out;
    }

    /**
     * @param  array<string, InterestCategory>  $categories
     */
    private function buatSoalMinat(array $categories): void
    {
        $options = [
            ['label' => 'Sangat tidak tertarik', 'score' => 0],
            ['label' => 'Tidak tertarik', 'score' => 1],
            ['label' => 'Netral', 'score' => 2],
            ['label' => 'Tertarik', 'score' => 3],
            ['label' => 'Sangat tertarik', 'score' => 4],
        ];

        foreach ($categories as $kode => $category) {
            InstrumentQuestion::query()->create([
                'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                'interest_category_id' => $category->id,
                'jenjang_target' => 'semua',
                'bobot' => 1,
                'question' => "Soal minat {$kode}",
                'options' => $options,
                'is_active' => true,
            ]);
        }
    }

    private function buatProdiTerverifikasi(InterestCategory $category): ProgramStudi
    {
        $prodi = ProgramStudi::query()->create([
            'institusi' => 'Politeknik Caltex Riau',
            'nama' => 'Teknik Informatika',
            'jenjang_pendidikan' => 'D4',
            'jurusan' => 'Teknik Informatika',
            'deskripsi' => 'Program studi informatika PCR.',
            'website_url' => 'https://pmb.pcr.ac.id',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $prodi->interestCategories()->sync([
            $category->id => ['relevansi' => 3],
        ]);

        return $prodi;
    }

    private function buatBidangKarier(InterestCategory $category): CareerField
    {
        $field = CareerField::query()->create([
            'nama' => 'Pengembang Web & Aplikasi',
            'deskripsi' => 'Pengembangan aplikasi web dan mobile.',
            'contoh_pekerjaan' => ['Web developer', 'Mobile developer'],
            'job_zone' => 3,
            'is_active' => true,
        ]);

        $field->interestCategories()->sync([
            $category->id => ['relevansi' => 3],
        ]);

        return $field;
    }

    /**
     * @return array<int, int>
     */
    private function jawabSemuaSoalDominan(int $dominantCategoryId): array
    {
        $answers = [];

        InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->where('is_active', true)
            ->get()
            ->each(function (InstrumentQuestion $question) use (&$answers, $dominantCategoryId) {
                $answers[$question->id] = $question->interest_category_id === $dominantCategoryId ? 4 : 1;
            });

        return $answers;
    }
}
