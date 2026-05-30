<?php

namespace Tests\Feature\Phase4;

use App\Models\ConsultationRequest;
use App\Models\PenilaianPelayanan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PenilaianPelayananTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_dapat_melihat_halaman_index_penilaian(): void
    {
        $this->actingAsSiswa()
            ->get(route('siswa.penilaian.index'))
            ->assertOk()
            ->assertViewIs('siswa.penilaian.index');
    }

    public function test_siswa_dapat_melihat_form_untuk_konseling_selesai(): void
    {
        [$siswa, $konseling] = $this->buatKonselingSelesai();

        $this->actingAs($siswa)
            ->get(route('siswa.penilaian.create', ['consultation' => $konseling->id]))
            ->assertOk()
            ->assertViewIs('siswa.penilaian.create');
    }

    public function test_siswa_tidak_bisa_akses_form_untuk_konseling_belum_selesai(): void
    {
        [$siswa, $konseling] = $this->buatKonselingPending();

        $this->actingAs($siswa)
            ->get(route('siswa.penilaian.create', ['consultation' => $konseling->id]))
            ->assertNotFound();
    }

    public function test_siswa_tidak_bisa_akses_form_konseling_milik_siswa_lain(): void
    {
        [, $konseling] = $this->buatKonselingSelesai();
        $siswaIni = $this->buatSiswa();

        $this->actingAs($siswaIni)
            ->get(route('siswa.penilaian.create', ['consultation' => $konseling->id]))
            ->assertNotFound();
    }

    public function test_siswa_berhasil_submit_penilaian_valid(): void
    {
        [$siswa, $konseling] = $this->buatKonselingSelesai();

        $this->actingAs($siswa)
            ->post(route('siswa.penilaian.store'), [
                'consultation_request_id' => $konseling->id,
                'skor_materi' => 4,
                'skor_cara' => 5,
                'skor_manfaat' => 4,
                'catatan' => 'Sangat membantu saya.',
            ])
            ->assertRedirect(route('siswa.penilaian.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('penilaian_pelayanan', [
            'consultation_request_id' => $konseling->id,
            'skor_materi' => 4,
            'skor_cara' => 5,
            'skor_manfaat' => 4,
        ]);
    }

    public function test_siswa_tidak_bisa_submit_penilaian_duplikat(): void
    {
        [$siswa, $konseling] = $this->buatKonselingSelesai();
        $student = $siswa->studentProfile;

        PenilaianPelayanan::factory()->create([
            'consultation_request_id' => $konseling->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($siswa)
            ->post(route('siswa.penilaian.store'), [
                'consultation_request_id' => $konseling->id,
                'skor_materi' => 3,
                'skor_cara' => 3,
                'skor_manfaat' => 3,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('penilaian_pelayanan', 1);
    }

    public function test_validasi_menolak_skor_di_luar_rentang(): void
    {
        [$siswa, $konseling] = $this->buatKonselingSelesai();

        $this->actingAs($siswa)
            ->post(route('siswa.penilaian.store'), [
                'consultation_request_id' => $konseling->id,
                'skor_materi' => 6,
                'skor_cara' => 0,
                'skor_manfaat' => 3,
            ])
            ->assertSessionHasErrors(['skor_materi', 'skor_cara']);
    }

    public function test_validasi_menolak_skor_yang_hilang(): void
    {
        [$siswa, $konseling] = $this->buatKonselingSelesai();

        $this->actingAs($siswa)
            ->post(route('siswa.penilaian.store'), [
                'consultation_request_id' => $konseling->id,
                'skor_cara' => 3,
                'skor_manfaat' => 3,
            ])
            ->assertSessionHasErrors(['skor_materi']);
    }

    public function test_guru_dapat_melihat_laporan_penilaian(): void
    {
        $guru = $this->buatGuru();

        $this->actingAs($guru)
            ->get(route('guru.penilaian.index'))
            ->assertOk()
            ->assertViewIs('guru.penilaian.index');
    }

    public function test_laporan_guru_tidak_menghasilkan_n_plus_1(): void
    {
        $guru = $this->buatGuru();

        for ($i = 0; $i < 5; $i++) {
            $siswa = $this->buatSiswa();
            $konseling = ConsultationRequest::query()->create([
                'student_id' => $siswa->id,
                'counselor_id' => $guru->id,
                'subject' => 'Konseling '.$i,
                'preferred_time' => 'Pagi',
                'status' => ConsultationRequest::STATUS_SELESAI,
                'scheduled_at' => now(),
            ]);

            PenilaianPelayanan::factory()->create([
                'consultation_request_id' => $konseling->id,
                'student_id' => $siswa->studentProfile->id,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($guru)->get(route('guru.penilaian.index'))->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(12, $queryCount,
            "N+1 detected: {$queryCount} queries untuk 5 record");
    }

    public function test_unauthenticated_diredirect_ke_login(): void
    {
        $this->get(route('siswa.penilaian.index'))
            ->assertRedirect(route('login'));
    }

    public function test_siswa_tidak_bisa_akses_laporan_guru(): void
    {
        $this->actingAsSiswa()
            ->get(route('guru.penilaian.index'))
            ->assertForbidden();
    }

    public function test_guru_tidak_bisa_akses_form_siswa(): void
    {
        $this->actingAs($this->buatGuru())
            ->get(route('siswa.penilaian.index'))
            ->assertForbidden();
    }

    private function buatSiswa(): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        Student::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
        ]);

        return $user->fresh(['studentProfile']);
    }

    private function actingAsSiswa(): static
    {
        return $this->actingAs($this->buatSiswa());
    }

    private function buatGuru(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    /** @return array{0: User, 1: ConsultationRequest} */
    private function buatKonselingSelesai(?User $siswa = null, ?User $guru = null): array
    {
        $siswa = $siswa ?? $this->buatSiswa();
        $guru = $guru ?? $this->buatGuru();

        $konseling = ConsultationRequest::query()->create([
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'subject' => 'Konseling selesai',
            'preferred_time' => 'Pagi',
            'status' => ConsultationRequest::STATUS_SELESAI,
            'scheduled_at' => now(),
        ]);

        return [$siswa, $konseling];
    }

    /** @return array{0: User, 1: ConsultationRequest} */
    private function buatKonselingPending(): array
    {
        $siswa = $this->buatSiswa();

        $konseling = ConsultationRequest::query()->create([
            'student_id' => $siswa->id,
            'counselor_id' => $this->buatGuru()->id,
            'subject' => 'Konseling pending',
            'preferred_time' => 'Sore',
            'status' => ConsultationRequest::STATUS_PENDING,
            'scheduled_at' => now(),
        ]);

        return [$siswa, $konseling];
    }
}
