<?php

namespace Tests\Feature\Phase5;

use App\Models\RaporBk;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Phase5IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_alur_rapor_end_to_end(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);

        $siswaUser = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $student = Student::query()->create([
            'user_id' => $siswaUser->id,
            'name' => $siswaUser->name,
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
        ]);

        $this->actingAs($guru)
            ->put(route('guru.rapor.update', $student), [
                'semester' => RaporBk::SEMESTER_GENAP,
                'tahun_ajaran' => '2025/2026',
                'perkembangan_akademik' => 'Meningkat.',
                'perkembangan_sosial' => 'Baik.',
                'perkembangan_psikologis' => 'Stabil.',
                'saran_tindak_lanjut' => 'Monitoring bulanan.',
                'status' => RaporBk::STATUS_FINAL,
            ])
            ->assertRedirect();

        $rapor = RaporBk::query()->first();
        $this->assertNotNull($rapor);

        $this->actingAs($guru)
            ->get(route('guru.rapor.pdf', $rapor))
            ->assertOk();

        $this->actingAs(User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]))
            ->get(route('admin.rapor.show', $rapor))
            ->assertOk();
    }

    public function test_route_phase4_dan_tim_lain_masih_ada(): void
    {
        $routes = [
            'siswa.penilaian.index',
            'guru.angket.index',
            'guru.consultations.index',
            'guru.rpls.index',
            'guru.instrument-results.index',
            'siswa.instruments.index',
        ];

        foreach ($routes as $name) {
            $this->assertTrue(Route::has($name), "Route hilang setelah Phase 5: {$name}");
        }
    }

    public function test_semua_route_phase5_butuh_autentikasi(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $student = Student::query()->create([
            'user_id' => User::factory()->create([
                'role' => User::ROLE_SISWA,
                'status' => User::STATUS_APPROVED,
            ])->id,
            'name' => 'Siswa Test',
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
        ]);
        $rapor = RaporBk::factory()->create([
            'counselor_id' => $guru->id,
            'student_id' => $student->id,
        ]);

        $urls = [
            route('guru.rapor.index'),
            route('guru.rapor.edit', ['student' => $student, 'semester' => RaporBk::SEMESTER_GANJIL, 'tahun_ajaran' => '2025/2026']),
            route('admin.rapor.index'),
            route('admin.rapor.show', $rapor),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }
}
