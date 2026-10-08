<?php

namespace Tests\Feature;

use App\Models\ConsultationRequest;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KonsultasiProdiTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_dapat_melihat_halaman_konsultasi_prodi(): void
    {
        $this->actingAs($this->buatSiswa())
            ->get(route('siswa.konsultasi-prodi.index'))
            ->assertOk()
            ->assertSee('Layanan Konsultasi Prodi Kuliah');
    }

    public function test_siswa_dapat_mengajukan_konsultasi_prodi(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $prodi = ProgramStudi::query()->create([
            'institusi' => 'Politeknik Caltex Riau',
            'nama' => 'Teknik Informatika',
            'jenjang_pendidikan' => 'D4',
            'jurusan' => 'Teknik Informatika',
            'deskripsi' => 'Program studi uji.',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->actingAs($siswa)
            ->post(route('siswa.konsultasi-prodi.store'), [
                'counselor_id' => $guru->id,
                'program_studi_id' => $prodi->id,
                'subject' => 'Cocokkah saya masuk TI?',
                'preferred_date' => now()->addDays(2)->toDateString(),
                'preferred_time' => '10:00',
                'details' => 'Ingin membahas hasil minat bakat terkait prodi TI PCR.',
            ])
            ->assertRedirect(route('siswa.konsultasi-prodi.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('consultation_requests', [
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'program_studi_id' => $prodi->id,
            'case_category' => ConsultationRequest::CASE_PRODI_KULIAH,
            'subject' => 'Cocokkah saya masuk TI?',
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);
    }

    public function test_admin_dapat_memfilter_konseling_per_kategori_prodi(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $prodi = ProgramStudi::query()->create([
            'institusi' => 'Politeknik Caltex Riau',
            'nama' => 'Sistem Informasi',
            'jenjang_pendidikan' => 'D3',
            'is_verified' => true,
            'is_active' => true,
        ]);

        ConsultationRequest::query()->create([
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'program_studi_id' => $prodi->id,
            'subject' => 'Tanya SI',
            'case_category' => ConsultationRequest::CASE_PRODI_KULIAH,
            'preferred_time' => 'Fleksibel',
            'details' => 'Detail uji admin filter.',
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.consultations.index', [
                'kategori' => ConsultationRequest::CASE_PRODI_KULIAH,
            ]))
            ->assertOk()
            ->assertSee('Tanya SI')
            ->assertSee('Prodi: Sistem Informasi');
    }

    public function test_siswa_tidak_bisa_ajukan_prodi_belum_terverifikasi(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $prodi = ProgramStudi::query()->create([
            'institusi' => 'Politeknik Caltex Riau',
            'nama' => 'Prodi Draft',
            'jenjang_pendidikan' => 'D3',
            'is_verified' => false,
            'is_active' => true,
        ]);

        $this->actingAs($siswa)
            ->post(route('siswa.konsultasi-prodi.store'), [
                'counselor_id' => $guru->id,
                'program_studi_id' => $prodi->id,
                'subject' => 'Tanya draft',
                'details' => 'Seharusnya ditolak karena prodi belum diverifikasi.',
            ])
            ->assertSessionHasErrors('program_studi_id');

        $this->assertDatabaseMissing('consultation_requests', [
            'student_id' => $siswa->id,
            'program_studi_id' => $prodi->id,
        ]);
    }

    public function test_guru_melihat_badge_prodi_di_daftar_konseling(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $prodi = ProgramStudi::query()->create([
            'institusi' => 'Politeknik Caltex Riau',
            'nama' => 'Teknik Elektro',
            'jenjang_pendidikan' => 'D4',
            'is_verified' => true,
            'is_active' => true,
        ]);

        ConsultationRequest::query()->create([
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'program_studi_id' => $prodi->id,
            'subject' => 'Konsultasi Elektro',
            'case_category' => ConsultationRequest::CASE_PRODI_KULIAH,
            'preferred_time' => 'Fleksibel',
            'details' => 'Detail uji guru badge prodi.',
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.consultations.index'))
            ->assertOk()
            ->assertSee('Konsultasi Elektro')
            ->assertSee('Prodi: Teknik Elektro');
    }

    private function buatSiswa(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);
    }
}
