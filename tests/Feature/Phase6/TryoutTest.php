<?php

namespace Tests\Feature\Phase6;

use App\Models\Kelas;
use App\Models\MasterQuestion;
use App\Models\Sekolah;
use App\Models\Student;
use App\Models\TryOut;
use App\Models\TryOutDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TryoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_dapat_melihat_daftar_tryout(): void
    {
        $this->actingAs($this->buatGuru())
            ->get(route('guru.tryout.index'))
            ->assertOk()
            ->assertViewIs('guru.tryout.index');
    }

    public function test_guru_dapat_membuat_tryout(): void
    {
        $guru = $this->buatGuru();
        $kelas = $this->buatKelas('X IPA 1');
        $soal = MasterQuestion::factory()->create([
            'kategori' => MasterQuestion::KATEGORI_TRYOUT,
            'tipe_input' => MasterQuestion::TIPE_SKALA,
            'is_active' => true,
        ]);

        $this->actingAs($guru)
            ->post(route('guru.tryout.store'), [
                'judul' => 'Tryout Semester 1',
                'deskripsi' => 'Latihan',
                'durasi_menit' => 30,
                'mulai_at' => now()->subHour()->format('Y-m-d\TH:i'),
                'selesai_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'status' => TryOut::STATUS_AKTIF,
                'kelas_ids' => [$kelas->id],
                'soal_ids' => [$soal->id],
            ])
            ->assertRedirect(route('guru.tryout.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('try_outs', [
            'counselor_id' => $guru->id,
            'judul' => 'Tryout Semester 1',
            'status' => TryOut::STATUS_AKTIF,
        ]);
    }

    public function test_siswa_dapat_mengerjakan_tryout_aktif(): void
    {
        [$siswa, $tryout, $soal] = $this->buatTryoutAktifUntukSiswa();

        $this->actingAs($siswa->user)
            ->get(route('siswa.tryout.show', $tryout))
            ->assertOk()
            ->assertViewIs('siswa.tryout.show');

        $this->actingAs($siswa->user)
            ->post(route('siswa.tryout.store', $tryout), [
                'jawaban' => [(string) $soal->id => '4'],
            ])
            ->assertRedirect(route('siswa.tryout.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('try_out_detail', [
            'try_out_id' => $tryout->id,
            'student_id' => $siswa->id,
        ]);

        $detail = TryOutDetail::query()->where('try_out_id', $tryout->id)->where('student_id', $siswa->id)->first();
        $this->assertNotNull($detail->submitted_at);
        $this->assertGreaterThan(0, (float) $detail->rata_skor);
    }

    public function test_siswa_tidak_bisa_kerjakan_ulang_tryout(): void
    {
        [$siswa, $tryout, $soal] = $this->buatTryoutAktifUntukSiswa();

        TryOutDetail::query()->create([
            'try_out_id' => $tryout->id,
            'student_id' => $siswa->id,
            'jawaban' => [(string) $soal->id => '5'],
            'rata_skor' => 100,
            'submitted_at' => now(),
        ]);

        $this->actingAs($siswa->user)
            ->get(route('siswa.tryout.show', $tryout))
            ->assertForbidden();
    }

    public function test_rute_tryout_terdaftar_di_menu_guru_dan_siswa(): void
    {
        $this->assertTrue(Route::has('guru.tryout.index'));
        $this->assertTrue(Route::has('siswa.tryout.index'));
    }

    /**
     * @return array{0: Student, 1: TryOut, 2: MasterQuestion}
     */
    private function buatTryoutAktifUntukSiswa(): array
    {
        $guru = $this->buatGuru();
        $kelas = $this->buatKelas('XI IPS 2');
        $siswa = $this->buatStudent($kelas->id);
        $soal = MasterQuestion::factory()->create([
            'kategori' => MasterQuestion::KATEGORI_TRYOUT,
            'tipe_input' => MasterQuestion::TIPE_SKALA,
            'is_active' => true,
        ]);

        $tryout = TryOut::query()->create([
            'counselor_id' => $guru->id,
            'judul' => 'Tryout Aktif',
            'durasi_menit' => 60,
            'mulai_at' => now()->subHour(),
            'selesai_at' => now()->addDay(),
            'soal_ids' => [$soal->id],
            'status' => TryOut::STATUS_AKTIF,
        ]);
        $tryout->kelas()->attach($kelas->id);

        return [$siswa, $tryout, $soal];
    }

    private function buatKelas(string $nama): Kelas
    {
        $sekolah = Sekolah::query()->create([
            'nama' => 'SMA Uji',
            'is_mou' => true,
            'is_active' => true,
        ]);

        return Kelas::query()->create([
            'sekolah_id' => $sekolah->id,
            'nama' => $nama,
        ]);
    }

    private function buatGuru(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    private function buatStudent(?int $kelasId = null): Student
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        return Student::query()->create([
            'user_id' => $user->id,
            'kelas_id' => $kelasId,
            'name' => $user->name,
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
        ]);
    }
}
