<?php

namespace Tests\Feature\Sosiometri;

use App\Models\SociometryQuestion;
use App\Models\SociometryResponse;
use App\Models\User;
use Database\Seeders\SociometryQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SociometryQuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'status' => User::STATUS_APPROVED]);
    }

    public function test_admin_dan_guru_dapat_mengelola_soal(): void
    {
        foreach ([
            ['admin.sociometry-questions', $this->user(User::ROLE_ADMIN)],
            ['guru.sociometry.questions', $this->user(User::ROLE_GURU)],
        ] as [$prefix, $user]) {
            $this->actingAs($user)->get(route($prefix.'.index'))->assertOk();

            $this->actingAs($user)->post(route($prefix.'.store'), [
                'pertanyaan' => 'Soal uji '.$prefix,
                'jenis' => SociometryQuestion::JENIS_POSITIF,
                'aspek' => SociometryQuestion::ASPEK_BELAJAR,
                'urutan' => 1,
                'is_active' => 1,
            ])->assertRedirect();

            $this->assertDatabaseHas('sociometry_questions', ['pertanyaan' => 'Soal uji '.$prefix]);
        }
    }

    public function test_siswa_tidak_dapat_mengakses_kelola_soal(): void
    {
        $siswa = $this->user(User::ROLE_SISWA);

        $this->actingAs($siswa)->get(route('guru.sociometry.questions.index'))->assertForbidden();
        $this->actingAs($siswa)->get(route('admin.sociometry-questions.index'))->assertForbidden();
    }

    public function test_seeder_menyiapkan_delapan_soal_empat_positif_empat_negatif(): void
    {
        $this->seed(SociometryQuestionSeeder::class);

        $this->assertSame(4, SociometryQuestion::where('jenis', SociometryQuestion::JENIS_POSITIF)->count());
        $this->assertSame(4, SociometryQuestion::where('jenis', SociometryQuestion::JENIS_NEGATIF)->count());
        $this->assertDatabaseCount('sociometry_questions', 8);
    }

    public function test_siswa_menjawab_soal_sosiometri_dan_tersimpan_per_soal(): void
    {
        $this->seed(SociometryQuestionSeeder::class);

        $siswa = $this->user(User::ROLE_SISWA);
        $teman = $this->user(User::ROLE_SISWA);

        $this->actingAs($siswa)->get(route('siswa.sociometry.index'))->assertOk()->assertSee('Pertanyaan Positif');

        $answers = [];
        foreach (SociometryQuestion::all() as $question) {
            $answers[$question->id] = ['chosen_student_id' => $teman->id, 'reason' => 'Alasan '.$question->id];
        }

        $this->actingAs($siswa)->post(route('siswa.sociometry.store'), ['answers' => $answers])->assertRedirect();

        $this->assertDatabaseCount('sociometry_responses', 8);
        $this->assertDatabaseHas('sociometry_responses', [
            'student_id' => $siswa->id,
            'relation_type' => SociometryResponse::TYPE_NEGATIF,
        ]);
    }
}
