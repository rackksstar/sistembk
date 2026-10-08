<?php

namespace Tests\Feature\Minat;

use App\Models\GuruBk;
use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruInstrumentResultScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_hanya_melihat_hasil_siswa_sekolahnya(): void
    {
        $sekolahA = Sekolah::query()->create([
            'nama' => 'SMA Negeri A',
            'is_mou' => true,
            'is_active' => true,
        ]);
        $sekolahB = Sekolah::query()->create([
            'nama' => 'SMA Negeri B',
            'is_mou' => true,
            'is_active' => true,
        ]);

        $kelasA = Kelas::query()->create([
            'sekolah_id' => $sekolahA->id,
            'nama' => 'XII IPA 1',
            'jenjang' => 'SMA',
            'tingkatan' => 'XII',
        ]);
        $kelasB = Kelas::query()->create([
            'sekolah_id' => $sekolahB->id,
            'nama' => 'XII IPA 2',
            'jenjang' => 'SMA',
            'tingkatan' => 'XII',
        ]);

        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        GuruBk::query()->create([
            'user_id' => $guru->id,
            'sekolah_id' => $sekolahA->id,
            'nip' => '198001012006041001',
        ]);

        $siswaA = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
            'name' => 'Siswa Sekolah A',
        ]);
        Student::query()->create([
            'user_id' => $siswaA->id,
            'name' => $siswaA->name,
            'nisn' => '1000000001',
            'birth_date' => '2008-01-01',
            'kelas_id' => $kelasA->id,
            'school' => $sekolahA->nama,
        ]);

        $siswaB = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
            'name' => 'Siswa Sekolah B',
        ]);
        Student::query()->create([
            'user_id' => $siswaB->id,
            'name' => $siswaB->name,
            'nisn' => '1000000002',
            'birth_date' => '2008-01-01',
            'kelas_id' => $kelasB->id,
            'school' => $sekolahB->nama,
        ]);

        InstrumentSubmission::query()->create([
            'student_id' => $siswaA->id,
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'jenjang' => 'SMA',
            'kode_minat' => 'RIA',
            'total_score' => 10,
            'result_label' => 'Teknik',
            'result_description' => 'Deskripsi A',
            'submitted_at' => now(),
        ]);

        InstrumentSubmission::query()->create([
            'student_id' => $siswaB->id,
            'category' => InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'jenjang' => 'SMA',
            'kode_minat' => 'SEC',
            'total_score' => 12,
            'result_label' => 'Sosial',
            'result_description' => 'Deskripsi B',
            'submitted_at' => now(),
        ]);

        $this->actingAs($guru)
            ->get(route('guru.instrument-results.index', ['module' => 'key']))
            ->assertOk()
            ->assertSee('Siswa Sekolah A')
            ->assertDontSee('Siswa Sekolah B');
    }
}
