<?php

namespace Tests\Feature\Minat;

use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstrumentRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_gaya_belajar_masih_memakai_label_skoring_lama(): void
    {
        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $questions = collect([
            'Saya lebih mudah memahami materi ketika melihat gambar.',
            'Saya lebih cepat mengingat setelah mendengar penjelasan.',
            'Saya senang belajar dengan praktik langsung.',
        ])->map(fn (string $text) => InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_GAYA_BELAJAR,
            'question' => $text,
            'options' => [
                ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
                ['label' => 'Tidak Sesuai', 'score' => 2],
                ['label' => 'Sesuai', 'score' => 3],
                ['label' => 'Sangat Sesuai', 'score' => 4],
            ],
            'is_active' => true,
        ]));

        // 4+4+4 = 12; max = 3*4 = 12 → 100% → Sangat Menonjol
        $answers = $questions->mapWithKeys(fn (InstrumentQuestion $q) => [$q->id => 3])->all();

        $response = $this->actingAs($siswa)
            ->post(route('siswa.instruments.store'), [
                'category' => InstrumentQuestion::CATEGORY_GAYA_BELAJAR,
                'answers' => $answers,
            ])
            ->assertSessionHas('success');

        // Tanpa section → halaman hasil sederhana; dengan section → strategi-belajar-result.
        $submission = InstrumentSubmission::query()
            ->where('student_id', $siswa->id)
            ->where('category', InstrumentQuestion::CATEGORY_GAYA_BELAJAR)
            ->latest('id')
            ->firstOrFail();

        $response->assertRedirect(route('siswa.instruments.strategi-belajar-result'));

        $this->assertDatabaseHas('instrument_submissions', [
            'id' => $submission->id,
            'total_score' => 12,
            'result_label' => 'Sangat Menonjol',
            'result_description' => 'Potensi atau kecenderungan siswa terlihat kuat pada instrumen ini.',
        ]);
    }

    public function test_submit_subset_soal_klasik_ditolak(): void
    {
        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $questions = collect([
            'Soal gaya 1',
            'Soal gaya 2',
        ])->map(fn (string $text) => InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_GAYA_BELAJAR,
            'question' => $text,
            'options' => [
                ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
                ['label' => 'Sangat Sesuai', 'score' => 4],
            ],
            'is_active' => true,
        ]));

        // Hanya jawab 1 dari 2 soal aktif → harus ditolak (cegah inflasi skor).
        $this->actingAs($siswa)
            ->from(route('siswa.instruments.index', ['category' => InstrumentQuestion::CATEGORY_GAYA_BELAJAR]))
            ->post(route('siswa.instruments.store'), [
                'category' => InstrumentQuestion::CATEGORY_GAYA_BELAJAR,
                'answers' => [
                    $questions->first()->id => 1,
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('answers');

        $this->assertDatabaseMissing('instrument_submissions', [
            'student_id' => $siswa->id,
            'category' => InstrumentQuestion::CATEGORY_GAYA_BELAJAR,
        ]);
    }

    public function test_submit_kepribadian_dihitung_sebagai_kode_mbti(): void
    {
        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $likert = InstrumentQuestion::KEPRIBADIAN_LIKERT_OPTIONS;

        $make = fn (string $axis, string $pole, string $text) => InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
            'question' => $text,
            'mbti_axis' => $axis,
            'mbti_pole' => $pole,
            'options' => $likert,
            'is_active' => true,
        ]);

        $ei = $make('EI', 'E', 'Saya bersemangat mengobrol dengan banyak teman.');
        $sn = $make('SN', 'N', 'Saya senang membayangkan kemungkinan masa depan.');
        $tf = $make('TF', 'T', 'Saya mengutamakan logika dalam keputusan.');
        $jp = $make('JP', 'J', 'Saya membuat rencana sebelum mengerjakan tugas.');

        // SS(5)→E+2, SS(5)→N+2, STS(1) pada kutub T→F+2, TS(2) pada kutub J→P+1
        // → kode "ENFP" — Sang Kampanyer. Keyakinan 100% pada 4 dimensi terisi.
        $response = $this->actingAs($siswa)
            ->post(route('siswa.instruments.store'), [
                'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
                'answers' => [$ei->id => 4, $sn->id => 4, $tf->id => 0, $jp->id => 1],
            ]);

        $submission = InstrumentSubmission::query()
            ->where('student_id', $siswa->id)
            ->where('category', InstrumentQuestion::CATEGORY_KEPRIBADIAN)
            ->firstOrFail();

        $response->assertRedirect(route('siswa.instruments.hasil', $submission));

        $this->assertSame(13, $submission->total_score);
        $this->assertSame('ENFP — Sang Kampanyer', $submission->result_label);
        $this->assertSame(100.0, (float) $submission->percentage);
        $this->assertSame('ENFP', $submission->category_scores['code']);
        $this->assertSame(['E' => 2, 'N' => 2, 'F' => 2, 'P' => 1], $submission->category_scores['tally']);
        $this->assertSame('Diplomat', $submission->category_scores['detail']['role']['name']);
        $this->assertNotEmpty($submission->category_scores['detail']['strengths']);
        $this->assertNotEmpty($submission->result_description);

        // Halaman hasil menampilkan rincian ala 16Personalities.
        $this->actingAs($siswa)
            ->get(route('siswa.instruments.hasil', $submission))
            ->assertOk()
            ->assertSee('Rincian 4 Dimensi Kepribadian', false)
            ->assertSee('Diplomat', false)
            ->assertSee('Kekuatanmu', false)
            ->assertSee('Tips belajar untukmu', false);
    }

    public function test_soal_kepribadian_lama_tanpa_dimensi_ditolak(): void
    {
        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        // Format lama: opsi Likert biasa tanpa kolom mbti_axis/mbti_pole.
        $legacy = InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
            'question' => 'Soal format lama tanpa dimensi MBTI.',
            'options' => [
                ['label' => 'Sangat Tidak Sesuai', 'score' => 1],
                ['label' => 'Sangat Sesuai', 'score' => 4],
            ],
            'is_active' => true,
        ]);

        $this->actingAs($siswa)
            ->post(route('siswa.instruments.store'), [
                'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
                'answers' => [$legacy->id => 0],
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('instrument_submissions', [
            'student_id' => $siswa->id,
            'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
        ]);
    }
}
