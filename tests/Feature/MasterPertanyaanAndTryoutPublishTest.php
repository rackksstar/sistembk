<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\MasterQuestion;
use App\Models\Sekolah;
use App\Models\Student;
use App\Models\TryOut;
use App\Models\TryOutDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterPertanyaanAndTryoutPublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_dapat_mengelola_master_pertanyaan_pilihan_ganda(): void
    {
        $guru = $this->buatGuru();

        $this->actingAs($guru)
            ->get(route('guru.master-pertanyaan.index'))
            ->assertOk()
            ->assertSee('Master Pertanyaan')
            ->assertSee('Tambah pertanyaan');

        $this->actingAs($guru)
            ->post(route('guru.master-pertanyaan.store'), [
                'kategori' => MasterQuestion::KATEGORI_TRYOUT,
                'teks_pertanyaan' => 'Ibukota Indonesia?',
                'tipe_input' => MasterQuestion::TIPE_PILIHAN_GANDA,
                'opsi' => ['Jakarta', 'Bandung', 'Surabaya'],
                'kunci_jawaban' => 'Jakarta',
                'is_active' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('master_questions', [
            'teks_pertanyaan' => 'Ibukota Indonesia?',
            'tipe_input' => MasterQuestion::TIPE_PILIHAN_GANDA,
            'kunci_jawaban' => 'Jakarta',
        ]);
    }

    public function test_siswa_melihat_input_sesuai_tipe_soal_tryout(): void
    {
        [$siswa, $tryout] = $this->buatTryoutDenganTigaTipe();

        $this->actingAs($siswa->user)
            ->get(route('siswa.tryout.show', $tryout))
            ->assertOk()
            ->assertSee('Pilih skor 1–5')
            ->assertSee('Jakarta')
            ->assertSee('Tulis jawabanmu');
    }

    public function test_siswa_tidak_melihat_skor_sebelum_hasil_dipublikasikan(): void
    {
        [$siswa, $tryout, $soalSkala] = $this->buatTryoutDenganTigaTipe();

        TryOutDetail::query()->create([
            'try_out_id' => $tryout->id,
            'student_id' => $siswa->id,
            'jawaban' => [(string) $soalSkala->id => '5'],
            'skor_per_soal' => [(string) $soalSkala->id => 100],
            'rata_skor' => 100,
            'submitted_at' => now(),
        ]);

        $this->actingAs($siswa->user)
            ->get(route('siswa.tryout.index'))
            ->assertOk()
            ->assertSee('Menunggu publikasi hasil')
            ->assertDontSee('Skor 100');

        $guru = User::query()->findOrFail($tryout->counselor_id);

        $this->actingAs($guru)
            ->patch(route('guru.tryout.publish', $tryout))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($siswa->user)
            ->get(route('siswa.tryout.index'))
            ->assertOk()
            ->assertSee('Skor 100.0');
    }

    public function test_guru_dapat_mengedit_nilai_isian_esai(): void
    {
        [$siswa, $tryout, $soalSkala, $soalPg, $soalIsian] = $this->buatTryoutDenganTigaTipe();
        $guru = User::query()->findOrFail($tryout->counselor_id);

        $detail = TryOutDetail::query()->create([
            'try_out_id' => $tryout->id,
            'student_id' => $siswa->id,
            'jawaban' => [
                (string) $soalSkala->id => '4',
                (string) $soalPg->id => 'Jakarta',
                (string) $soalIsian->id => 'Jawaban esai siswa',
            ],
            'skor_per_soal' => [
                (string) $soalSkala->id => 80,
                (string) $soalPg->id => 100,
                (string) $soalIsian->id => null,
            ],
            'rata_skor' => 90,
            'submitted_at' => now(),
        ]);

        $this->actingAs($guru)
            ->patch(route('guru.tryout.scores', [$tryout, $detail]), [
                'skor' => [
                    (string) $soalSkala->id => 80,
                    (string) $soalPg->id => 100,
                    (string) $soalIsian->id => 70,
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $detail->refresh();
        $this->assertSame(70.0, $detail->skorUntuk($soalIsian->id));
        $this->assertEqualsWithDelta(83.3, (float) $detail->rata_skor, 0.1);
    }

    /**
     * @return array{0: Student, 1: TryOut, 2: MasterQuestion, 3: MasterQuestion, 4: MasterQuestion}
     */
    private function buatTryoutDenganTigaTipe(): array
    {
        $guru = $this->buatGuru();
        $kelas = $this->buatKelas('XI IPA 9');
        $siswa = $this->buatStudent($kelas->id);

        $soalSkala = MasterQuestion::factory()->create([
            'kategori' => MasterQuestion::KATEGORI_TRYOUT,
            'tipe_input' => MasterQuestion::TIPE_SKALA,
            'teks_pertanyaan' => 'Seberapa siap kamu?',
            'is_active' => true,
        ]);
        $soalPg = MasterQuestion::factory()->create([
            'kategori' => MasterQuestion::KATEGORI_TRYOUT,
            'tipe_input' => MasterQuestion::TIPE_PILIHAN_GANDA,
            'teks_pertanyaan' => 'Ibukota?',
            'opsi' => ['Jakarta', 'Bandung'],
            'kunci_jawaban' => 'Jakarta',
            'is_active' => true,
        ]);
        $soalIsian = MasterQuestion::factory()->create([
            'kategori' => MasterQuestion::KATEGORI_TRYOUT,
            'tipe_input' => MasterQuestion::TIPE_ISIAN,
            'teks_pertanyaan' => 'Jelaskan minatmu',
            'is_active' => true,
        ]);

        $tryout = TryOut::query()->create([
            'counselor_id' => $guru->id,
            'judul' => 'Tryout Multi Tipe',
            'durasi_menit' => 45,
            'mulai_at' => now()->subHour(),
            'selesai_at' => now()->addDay(),
            'soal_ids' => [$soalSkala->id, $soalPg->id, $soalIsian->id],
            'status' => TryOut::STATUS_AKTIF,
            'hasil_dipublikasikan' => false,
        ]);
        $tryout->kelas()->attach($kelas->id);

        return [$siswa, $tryout, $soalSkala, $soalPg, $soalIsian];
    }

    private function buatKelas(string $nama): Kelas
    {
        $sekolah = Sekolah::query()->create([
            'nama' => 'SMA Uji Master',
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
