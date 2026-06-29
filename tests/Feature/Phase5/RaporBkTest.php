<?php

namespace Tests\Feature\Phase5;

use App\Models\RaporBk;
use App\Models\Student;
use App\Models\User;
use App\Services\RaporBkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RaporBkTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_dapat_melihat_daftar_rapor(): void
    {
        $this->actingAs($this->buatGuru())
            ->get(route('guru.rapor.index'))
            ->assertOk()
            ->assertViewIs('guru.rapor.index');
    }

    public function test_daftar_rapor_guru_tidak_menghasilkan_n_plus_1(): void
    {
        $guru = $this->buatGuru();

        for ($i = 0; $i < 5; $i++) {
            $this->buatStudent();
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($guru)->get(route('guru.rapor.index'))->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(14, $queryCount,
            "N+1 detected: {$queryCount} queries untuk 5 siswa");
    }

    public function test_guru_dapat_membuka_form_edit_rapor(): void
    {
        $guru = $this->buatGuru();
        $student = $this->buatStudent();

        $this->actingAs($guru)
            ->get(route('guru.rapor.edit', [
                'student' => $student,
                'semester' => RaporBk::SEMESTER_GANJIL,
                'tahun_ajaran' => '2025/2026',
            ]))
            ->assertOk()
            ->assertViewIs('guru.rapor.edit');
    }

    public function test_guru_dapat_simpan_rapor_baru(): void
    {
        $guru = $this->buatGuru();
        $student = $this->buatStudent();

        $this->actingAs($guru)
            ->put(route('guru.rapor.update', $student), [
                'semester' => RaporBk::SEMESTER_GANJIL,
                'tahun_ajaran' => '2025/2026',
                'perkembangan_akademik' => 'Baik dalam pelajaran.',
                'perkembangan_sosial' => 'Aktif di kelas.',
                'perkembangan_psikologis' => 'Stabil.',
                'saran_tindak_lanjut' => 'Lanjutkan konseling rutin.',
                'catatan_guru' => null,
                'status' => RaporBk::STATUS_DRAFT,
            ])
            ->assertRedirect(route('guru.rapor.index', [
                'semester' => RaporBk::SEMESTER_GANJIL,
                'tahun_ajaran' => '2025/2026',
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('rapor_bk', [
            'student_id' => $student->id,
            'counselor_id' => $guru->id,
            'semester' => RaporBk::SEMESTER_GANJIL,
            'tahun_ajaran' => '2025/2026',
            'status' => RaporBk::STATUS_DRAFT,
        ]);
    }

    public function test_update_or_create_tidak_duplikat_per_semester(): void
    {
        $guru = $this->buatGuru();
        $student = $this->buatStudent();
        $service = app(RaporBkService::class);

        $service->upsertForStudent($guru->id, $student, RaporBk::SEMESTER_GANJIL, '2025/2026', [
            'perkembangan_akademik' => 'Versi 1',
            'status' => RaporBk::STATUS_DRAFT,
        ]);

        $service->upsertForStudent($guru->id, $student, RaporBk::SEMESTER_GANJIL, '2025/2026', [
            'perkembangan_akademik' => 'Versi 2',
            'status' => RaporBk::STATUS_FINAL,
        ]);

        $this->assertDatabaseCount('rapor_bk', 1);
        $this->assertDatabaseHas('rapor_bk', [
            'student_id' => $student->id,
            'perkembangan_akademik' => 'Versi 2',
            'status' => RaporBk::STATUS_FINAL,
        ]);
    }

    public function test_guru_dapat_unduh_pdf_rapor_miliknya(): void
    {
        $guru = $this->buatGuru();
        $student = $this->buatStudent();

        $rapor = RaporBk::factory()->create([
            'student_id' => $student->id,
            'counselor_id' => $guru->id,
            'status' => RaporBk::STATUS_FINAL,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.rapor.pdf', $rapor))
            ->assertOk();
    }

    public function test_guru_tidak_bisa_unduh_pdf_rapor_guru_lain(): void
    {
        $rapor = RaporBk::factory()->create();

        $this->actingAs($this->buatGuru())
            ->get(route('guru.rapor.pdf', $rapor))
            ->assertForbidden();
    }

    public function test_admin_dapat_pantau_daftar_rapor(): void
    {
        RaporBk::factory()->create();

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.rapor.index'))
            ->assertOk()
            ->assertViewIs('admin.rapor.index');
    }

    public function test_admin_dapat_lihat_detail_rapor_read_only(): void
    {
        $rapor = RaporBk::factory()->create();

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.rapor.show', $rapor))
            ->assertOk()
            ->assertViewIs('admin.rapor.show');
    }

    public function test_admin_dapat_unduh_pdf_rapor(): void
    {
        $rapor = RaporBk::factory()->create();

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.rapor.pdf', $rapor))
            ->assertOk();
    }

    public function test_siswa_tidak_bisa_akses_modul_rapor_guru(): void
    {
        $student = $this->buatStudent();
        $siswa = $student->user;

        $this->actingAs($siswa)
            ->get(route('guru.rapor.index'))
            ->assertForbidden();
    }

    public function test_validasi_tahun_ajaran_format(): void
    {
        $guru = $this->buatGuru();
        $student = $this->buatStudent();

        $this->actingAs($guru)
            ->put(route('guru.rapor.update', $student), [
                'semester' => RaporBk::SEMESTER_GANJIL,
                'tahun_ajaran' => 'invalid',
                'status' => RaporBk::STATUS_DRAFT,
            ])
            ->assertSessionHasErrors('tahun_ajaran');
    }

    private function buatGuru(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    private function buatAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    private function buatStudent(): Student
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        return Student::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
        ]);
    }
}
