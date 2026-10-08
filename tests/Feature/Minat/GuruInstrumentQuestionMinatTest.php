<?php

namespace Tests\Feature\Minat;

use App\Models\InstrumentAnswer;
use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use App\Models\InterestCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruInstrumentQuestionMinatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_dapat_membuat_soal_minat_dengan_kategori_bobot_dan_opsi(): void
    {
        $guru = $this->buatGuru();
        $category = InterestCategory::query()->create([
            'kode' => 'R',
            'nama' => 'Teknik',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($guru)
            ->post(route('guru.instrument-questions.store'), [
                'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                'talent_code' => 'R',
                'jenjang_target' => 'semua',
                'bobot' => 2,
                'question' => 'Memperbaiki mesin',
                'is_active' => 1,
                'options' => InstrumentQuestion::TALENTS_LIKERT_OPTIONS,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('instrument_questions', [
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'interest_category_id' => $category->id,
            'talent_code' => 'R',
            'bobot' => 2,
            'jenjang_target' => 'semua',
            'question' => 'Memperbaiki mesin',
            'created_by' => $guru->id,
        ]);
    }

    public function test_soal_minat_wajib_punya_talent_code(): void
    {
        $guru = $this->buatGuru();

        $this->actingAs($guru)
            ->post(route('guru.instrument-questions.store'), [
                'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                'jenjang_target' => 'SMA',
                'bobot' => 1,
                'question' => 'Soal tanpa kode RIASEC',
                'is_active' => 1,
                'options' => [
                    ['label' => 'Ya', 'score' => 1],
                    ['label' => 'Tidak', 'score' => 0],
                ],
            ])
            ->assertSessionHasErrors(['talent_code', 'interest_category_id']);
    }

    public function test_hapus_soal_yang_punya_jawaban_memakai_soft_delete(): void
    {
        $guru = $this->buatGuru();
        $category = InterestCategory::query()->create([
            'kode' => 'I',
            'nama' => 'Teknologi & Sains',
            'urutan' => 2,
            'is_active' => true,
        ]);

        $question = InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'interest_category_id' => $category->id,
            'jenjang_target' => 'semua',
            'bobot' => 1,
            'question' => 'Soal yang sudah dijawab',
            'options' => [
                ['label' => 'Ya', 'score' => 1],
                ['label' => 'Tidak', 'score' => 0],
            ],
            'is_active' => true,
            'created_by' => $guru->id,
        ]);

        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $submission = InstrumentSubmission::query()->create([
            'student_id' => $siswa->id,
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'total_score' => 1,
            'result_label' => 'Teknik',
            'result_description' => 'Deskripsi',
            'submitted_at' => now(),
        ]);

        InstrumentAnswer::query()->create([
            'instrument_submission_id' => $submission->id,
            'instrument_question_id' => $question->id,
            'answer_label' => 'Ya',
            'score' => 1,
        ]);

        $this->actingAs($guru)
            ->delete(route('guru.instrument-questions.destroy', $question))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSoftDeleted('instrument_questions', ['id' => $question->id]);
    }

    public function test_hapus_soal_tanpa_jawaban_force_delete(): void
    {
        $guru = $this->buatGuru();
        $category = InterestCategory::query()->create([
            'kode' => 'A',
            'nama' => 'Kreatif',
            'urutan' => 3,
            'is_active' => true,
        ]);

        $question = InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'interest_category_id' => $category->id,
            'jenjang_target' => 'semua',
            'bobot' => 1,
            'question' => 'Soal belum dijawab',
            'options' => [
                ['label' => 'Ya', 'score' => 1],
                ['label' => 'Tidak', 'score' => 0],
            ],
            'is_active' => true,
            'created_by' => $guru->id,
        ]);

        $this->actingAs($guru)
            ->delete(route('guru.instrument-questions.destroy', $question))
            ->assertRedirect();

        $this->assertDatabaseMissing('instrument_questions', ['id' => $question->id]);
    }

    private function buatGuru(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
    }
}
