<?php

namespace Tests\Feature\Phase4;

use App\Models\ConsultationRequest;
use App\Models\MasterQuestion;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Phase4IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_alur_penilaian_end_to_end(): void
    {
        $guru = User::factory()->create(['role' => User::ROLE_GURU, 'status' => User::STATUS_APPROVED]);
        $siswa = User::factory()->create(['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED]);
        $student = Student::query()->create([
            'user_id' => $siswa->id,
            'name' => $siswa->name,
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
        ]);

        $konseling = ConsultationRequest::query()->create([
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'subject' => 'Konseling integrasi',
            'preferred_time' => 'Pagi',
            'status' => ConsultationRequest::STATUS_SELESAI,
            'scheduled_at' => now(),
        ]);

        $this->actingAs($siswa->fresh(['studentProfile']))
            ->post(route('siswa.penilaian.store'), [
                'consultation_request_id' => $konseling->id,
                'skor_materi' => 5,
                'skor_cara' => 4,
                'skor_manfaat' => 5,
                'catatan' => 'Sangat bermanfaat.',
            ])
            ->assertRedirect(route('siswa.penilaian.index'));

        $this->actingAs($guru)
            ->get(route('guru.penilaian.index'))
            ->assertOk();

        $this->assertDatabaseHas('penilaian_pelayanan', [
            'consultation_request_id' => $konseling->id,
            'student_id' => $student->id,
            'skor_materi' => 5,
        ]);
    }

    public function test_alur_angket_end_to_end(): void
    {
        $guru = User::factory()->create(['role' => User::ROLE_GURU, 'status' => User::STATUS_APPROVED]);
        $siswa = User::factory()->create(['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED]);
        $student = Student::query()->create([
            'user_id' => $siswa->id,
            'name' => $siswa->name,
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
        ]);

        $soal = MasterQuestion::factory()->create([
            'kategori' => MasterQuestion::KATEGORI_ANGKET,
            'is_active' => true,
        ]);

        $this->actingAs($siswa->fresh(['studentProfile']))
            ->post(route('siswa.angket.store'), [
                'jawaban' => [$soal->id => 'Sangat setuju dengan program BK.'],
            ])
            ->assertRedirect(route('siswa.angket.index'));

        $this->assertDatabaseHas('respons_angket', [
            'student_id' => $student->id,
            'master_question_id' => $soal->id,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.angket.pdf', $student))
            ->assertOk();
    }

    public function test_semua_route_phase4_butuh_autentikasi(): void
    {
        $routes = [
            ['GET', route('siswa.penilaian.index')],
            ['GET', route('guru.penilaian.index')],
            ['GET', route('siswa.angket.index')],
            ['GET', route('guru.angket.index')],
        ];

        foreach ($routes as [$method, $url]) {
            $this->call($method, $url)->assertRedirect(route('login'));
        }
    }

    public function test_route_phase_sebelumnya_masih_ada(): void
    {
        $routeNames = [
            'siswa.consultations.index',
            'guru.consultations.index',
            'admin.consultations.index',
            'guru.consultations.events',
        ];

        foreach ($routeNames as $name) {
            $this->assertTrue(
                Route::has($name),
                "Route Phase sebelumnya hilang: {$name}"
            );
        }
    }

    public function test_legacy_service_feedback_tidak_broken(): void
    {
        $siswa = User::factory()->create(['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED]);
        Student::query()->create([
            'user_id' => $siswa->id,
            'name' => $siswa->name,
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
        ]);

        $this->actingAs($siswa)
            ->get(route('siswa.feedback.create'))
            ->assertRedirect(route('siswa.penilaian.index'));
    }
}
