<?php

namespace Tests\Feature\Sosiometri;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\SociometryQuestion;
use App\Models\SociometryResponse;
use App\Models\SosiometryInstrument;
use App\Models\Student;
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

        [, $studentProfile, $kelas] = $this->buatGuruDanSiswaTerhubung();
        $siswa = $studentProfile->user;
        $teman = $this->user(User::ROLE_SISWA);
        SosiometryInstrument::query()->create([
            'kelas_id' => $kelas->id,
            'is_active' => true,
        ]);

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

    public function test_siswa_kelas_nonaktif_tidak_dapat_membuka_halaman_atau_mengirim_sosiometri(): void
    {
        [, $studentProfile, $kelas] = $this->buatGuruDanSiswaTerhubung();
        SosiometryInstrument::query()->create([
            'kelas_id' => $kelas->id,
            'is_active' => false,
        ]);

        $siswa = $studentProfile->user;

        $this->actingAs($siswa)
            ->get(route('siswa.sociometry.index'))
            ->assertForbidden()
            ->assertSee('Akses Ditolak')
            ->assertSee('Error 403')
            ->assertSee('Kembali')
            ->assertSee('Instrumen sosiometri untuk kelas Anda sedang dinonaktifkan.');

        $this->actingAs($siswa)
            ->post(route('siswa.sociometry.store'), ['answers' => []])
            ->assertForbidden();
    }

    public function test_siswa_tidak_dapat_mengakses_sosiometri_jika_kelas_belum_diaktifkan(): void
    {
        [, $studentProfile] = $this->buatGuruDanSiswaTerhubung();

        $this->actingAs($studentProfile->user)
            ->get(route('siswa.sociometry.index'))
            ->assertForbidden();
    }

    public function test_siswa_tanpa_profil_kelas_tidak_dapat_mengakses_sosiometri(): void
    {
        $siswa = $this->user(User::ROLE_SISWA);

        $this->actingAs($siswa)
            ->get(route('siswa.sociometry.index'))
            ->assertForbidden();
    }

    public function test_guru_hanya_melihat_peta_dan_instrumen_di_sekolahnya(): void
    {
        [$guru, $siswaProfile, $kelas] = $this->buatGuruDanSiswaTerhubung();
        [$kelasLain, $siswaLain] = $this->buatSiswaDiSekolahLain();

        SociometryResponse::query()->create([
            'student_id' => $siswaProfile->user_id,
            'chosen_student_id' => $siswaLain->id,
            'relation_type' => SociometryResponse::TYPE_POSITIF,
            'reason' => 'Relasi lintas sekolah',
        ]);

        $this->actingAs($guru)
            ->get(route('guru.sociometry.index'))
            ->assertOk()
            ->assertSee($kelas->nama)
            ->assertSee($siswaProfile->name)
            ->assertDontSee($kelasLain->nama)
            ->assertDontSee($siswaLain->name)
            ->assertDontSee('Relasi lintas sekolah');

        $this->actingAs($guru)
            ->get(route('guru.sociometry.manage'))
            ->assertOk()
            ->assertSee($kelas->nama)
            ->assertDontSee($kelasLain->nama);
    }

    public function test_guru_tidak_dapat_membuka_detail_atau_export_siswa_dari_sekolah_lain(): void
    {
        [$guru] = $this->buatGuruDanSiswaTerhubung();
        [, $siswaLain] = $this->buatSiswaDiSekolahLain();

        $this->actingAs($guru)
            ->get(route('guru.sociometry.index', ['kelas_id' => $siswaLain->studentProfile->kelas_id]))
            ->assertNotFound();

        $this->actingAs($guru)
            ->get(route('guru.sociometry.export', ['kelas_id' => $siswaLain->studentProfile->kelas_id]))
            ->assertNotFound();

        $this->actingAs($guru)
            ->get(route('guru.sociometry.show', $siswaLain->id))
            ->assertNotFound();

        $this->actingAs($guru)
            ->get(route('guru.sociometry.export.student', $siswaLain->id))
            ->assertNotFound();
    }

    public function test_guru_tidak_dapat_mengubah_status_instrumen_kelas_sekolah_lain(): void
    {
        [$guru] = $this->buatGuruDanSiswaTerhubung();
        [$kelasLain] = $this->buatSiswaDiSekolahLain();

        $this->actingAs($guru)
            ->post(route('guru.sociometry.manage.toggle'), [
                'kelas_id' => $kelasLain->id,
                'is_active' => true,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('sosiometry_instruments', [
            'kelas_id' => $kelasLain->id,
        ]);
    }

    /**
     * @return array{0: Kelas, 1: User}
     */
    private function buatSiswaDiSekolahLain(): array
    {
        $sekolah = Sekolah::query()->create([
            'nama' => 'SMA Sekolah Lain',
            'is_mou' => true,
        ]);
        $kelas = Kelas::query()->create([
            'sekolah_id' => $sekolah->id,
            'nama' => 'XI IPA Lain',
        ]);
        $siswa = $this->user(User::ROLE_SISWA);
        Student::query()->create([
            'user_id' => $siswa->id,
            'name' => 'Siswa dari Sekolah Lain',
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
            'kelas_id' => $kelas->id,
        ]);

        return [$kelas, $siswa];
    }
}
