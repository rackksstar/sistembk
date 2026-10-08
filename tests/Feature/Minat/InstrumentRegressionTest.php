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

        $ei = InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
            'question' => 'Ketika berada di tempat ramai, saya...',
            'options' => [
                ['label' => 'merasa berenergi dan senang mengobrol', 'pole' => 'E'],
                ['label' => 'lebih suka menyendiri', 'pole' => 'I'],
            ],
            'is_active' => true,
        ]);

        $sn = InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
            'question' => 'Saat belajar hal baru, saya lebih suka...',
            'options' => [
                ['label' => 'contoh nyata dan langkah yang jelas', 'pole' => 'S'],
                ['label' => 'memahami konsep besar dan kemungkinan', 'pole' => 'N'],
            ],
            'is_active' => true,
        ]);

        // E (1) + N (1) → dimensi kosong lainnya jatuh ke kutub pertama (T, J)
        // → kode "ENTJ" — Sang Komandan. Keyakinan 100% pada 2 dimensi terisi.
        $response = $this->actingAs($siswa)
            ->post(route('siswa.instruments.store'), [
                'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
                'answers' => [$ei->id => 0, $sn->id => 1],
            ]);

        $submission = InstrumentSubmission::query()
            ->where('student_id', $siswa->id)
            ->where('category', InstrumentQuestion::CATEGORY_KEPRIBADIAN)
            ->firstOrFail();

        $response->assertRedirect(route('siswa.instruments.hasil', $submission));

        $this->assertSame(2, $submission->total_score);
        $this->assertSame('ENTJ — Sang Komandan', $submission->result_label);
        $this->assertSame(100.0, (float) $submission->percentage);
        $this->assertSame('ENTJ', $submission->category_scores['code']);
        $this->assertSame(['E' => 1, 'N' => 1], $submission->category_scores['tally']);
        $this->assertNotEmpty($submission->result_description);
    }

    public function test_soal_kepribadian_lama_tanpa_kutub_ditolak(): void
    {
        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $legacy = InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
            'question' => 'Soal format lama tanpa huruf kutub.',
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
