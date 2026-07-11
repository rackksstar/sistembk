<?php

namespace Tests\Feature\Phase4;

use App\Models\MasterQuestion;
use App\Models\ResponsAngket;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AngketTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_dapat_melihat_halaman_angket(): void
    {
        $this->actingAsSiswa()
            ->get(route('siswa.angket.index'))
            ->assertOk()
            ->assertViewIs('siswa.angket.index');
    }

    public function test_halaman_isi_hanya_tampilkan_soal_aktif_kategori_angket(): void
    {
        $siswa = $this->buatSiswa();
        MasterQuestion::factory()->create(['kategori' => MasterQuestion::KATEGORI_ANGKET, 'is_active' => true]);
        MasterQuestion::factory()->create(['kategori' => MasterQuestion::KATEGORI_ANGKET, 'is_active' => false]);
        MasterQuestion::factory()->create(['kategori' => MasterQuestion::KATEGORI_TRYOUT, 'is_active' => true]);

        $response = $this->actingAs($siswa)->get(route('siswa.angket.show'));
        $response->assertOk();

        $pertanyaan = $response->viewData('pertanyaan');
        $this->assertCount(1, $pertanyaan, 'Hanya 1 soal aktif kategori angket yang boleh tampil');
    }

    public function test_siswa_berhasil_submit_jawaban_angket(): void
    {
        $siswa = $this->buatSiswa();
        $soal1 = MasterQuestion::factory()->create(['kategori' => MasterQuestion::KATEGORI_ANGKET, 'is_active' => true]);
        $soal2 = MasterQuestion::factory()->create(['kategori' => MasterQuestion::KATEGORI_ANGKET, 'is_active' => true]);

        $this->actingAs($siswa)
            ->post(route('siswa.angket.store'), [
                'jawaban' => [
                    $soal1->id => 'Sangat setuju',
                    $soal2->id => 'Setuju',
                ],
            ])
            ->assertRedirect(route('siswa.angket.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('respons_angket', 2);
    }

    public function test_submit_angket_bersifat_idempotent(): void
    {
        $siswa = $this->buatSiswa();
        $student = $siswa->studentProfile;
        $soal = MasterQuestion::factory()->create(['kategori' => MasterQuestion::KATEGORI_ANGKET, 'is_active' => true]);

        ResponsAngket::query()->create([
            'student_id' => $student->id,
            'master_question_id' => $soal->id,
            'jawaban' => 'Jawaban lama',
        ]);

        $this->actingAs($siswa)
            ->post(route('siswa.angket.store'), [
                'jawaban' => [$soal->id => 'Jawaban baru'],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('respons_angket', 1);
        $this->assertDatabaseHas('respons_angket', [
            'student_id' => $student->id,
            'jawaban' => 'Jawaban baru',
        ]);
    }

    public function test_soal_tidak_valid_dilewati_tidak_error(): void
    {
        $siswa = $this->buatSiswa();
        $soalValid = MasterQuestion::factory()->create(['kategori' => MasterQuestion::KATEGORI_ANGKET, 'is_active' => true]);
        $soalTryout = MasterQuestion::factory()->create(['kategori' => MasterQuestion::KATEGORI_TRYOUT, 'is_active' => true]);

        $this->actingAs($siswa)
            ->post(route('siswa.angket.store'), [
                'jawaban' => [
                    $soalValid->id => 'Jawaban valid',
                    $soalTryout->id => 'Coba masukkan soal tryout',
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('respons_angket', 1);
    }

    public function test_guru_dapat_melihat_laporan_angket(): void
    {
        $this->actingAs($this->buatGuru())
            ->get(route('guru.angket.index'))
            ->assertOk()
            ->assertViewIs('guru.angket.index');
    }

    public function test_guru_dapat_melihat_detail_angket_siswa(): void
    {
        [$guru, $student] = $this->buatGuruDanSiswaTerhubung();

        $this->actingAs($guru)
            ->get(route('guru.angket.show', $student))
            ->assertOk()
            ->assertViewIs('guru.angket.show');
    }

    public function test_laporan_angket_guru_tidak_menghasilkan_n_plus_1(): void
    {
        $guru = $this->buatGuru();
        $soal = MasterQuestion::factory()->create(['kategori' => MasterQuestion::KATEGORI_ANGKET, 'is_active' => true]);

        for ($i = 0; $i < 5; $i++) {
            $siswa = $this->buatSiswa();
            ResponsAngket::query()->create([
                'student_id' => $siswa->studentProfile->id,
                'master_question_id' => $soal->id,
                'jawaban' => 'Jawaban '.$i,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($guru)->get(route('guru.angket.index'))->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(12, $queryCount,
            "N+1 detected: {$queryCount} queries untuk 5 siswa");
    }

    public function test_guru_dapat_download_pdf_angket_siswa(): void
    {
        [$guru, $student] = $this->buatGuruDanSiswaTerhubung();

        $this->actingAs($guru)
            ->get(route('guru.angket.pdf', $student))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_siswa_tidak_bisa_download_pdf(): void
    {
        $siswa = $this->buatSiswa();
        $student = $siswa->studentProfile;

        $this->actingAs($siswa)
            ->get(route('guru.angket.pdf', $student))
            ->assertForbidden();
    }

    public function test_unauthenticated_diredirect_ke_login_angket(): void
    {
        $this->get(route('siswa.angket.index'))
            ->assertRedirect(route('login'));
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
}
