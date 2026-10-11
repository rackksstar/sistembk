<?php

namespace Tests\Feature\Phase3;

use App\Models\ConsultationRequest;
use App\Models\Rpl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndividualConsultationReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_dapat_membuka_laporan_individu_miliknya(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $guruLain = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $milikGuru = $this->buatKonsultasi($guru, 'Laporan guru ini');
        $milikGuruLain = $this->buatKonsultasi($guruLain, 'Laporan guru lain');
        $konsultasiKelompok = $this->buatKonsultasi($guru, 'Konseling kelompok', $this->buatRpl($guru, Rpl::TYPE_KELOMPOK));
        $this->isiLaporan($milikGuru, 'Laporan guru ini');
        $this->isiLaporan($milikGuruLain, 'Laporan guru lain');
        $this->isiLaporan($konsultasiKelompok, 'Konseling kelompok');

        $this->actingAs($guru)
            ->get(route('guru.individual-reports.index'))
            ->assertOk()
            ->assertViewIs('admin.individual-reports.index')
            ->assertSee('Laporan guru ini')
            ->assertDontSee('Laporan guru lain')
            ->assertDontSee('Konseling kelompok')
            ->assertSee('Detail Laporan Individu')
            ->assertSee(route('guru.individual-reports.print', $milikGuru))
            ->assertDontSee(route('guru.individual-reports.print', $milikGuruLain));
    }

    public function test_filter_rpl_hanya_mengambil_konsultasi_individu_dengan_rpl_terpilih(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $rplIndividu = $this->buatRpl($guru, Rpl::TYPE_INDIVIDU);
        $terkaitRpl = $this->buatKonsultasi($guru, 'Konseling terkait RPL', $rplIndividu);
        $tanpaRpl = $this->buatKonsultasi($guru, 'Konseling tanpa RPL');
        $this->isiLaporan($terkaitRpl, 'Laporan terkait RPL');
        $this->isiLaporan($tanpaRpl, 'Laporan tanpa RPL');

        $this->actingAs($guru)
            ->get(route('guru.individual-reports.index', ['rpl_id' => $rplIndividu->id]))
            ->assertOk()
            ->assertSee('Konseling terkait RPL')
            ->assertDontSee('Konseling tanpa RPL');
    }

    public function test_guru_tidak_dapat_mencetak_laporan_milik_guru_lain(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $guruLain = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $konsultasi = $this->buatKonsultasi($guruLain, 'Laporan guru lain');

        $this->actingAs($guru)
            ->get(route('guru.individual-reports.print', $konsultasi))
            ->assertForbidden();
    }

    public function test_admin_tetap_dapat_membuka_laporan_individu(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $konsultasi = $this->buatKonsultasi($guru, 'Laporan konseling');
        $this->isiLaporan($konsultasi, 'Laporan konseling');

        $this->actingAs($admin)
            ->get(route('admin.individual-reports.index'))
            ->assertOk()
            ->assertSee('Laporan konseling');
    }

    public function test_admin_tidak_dapat_mencetak_konsultasi_kelompok_sebagai_laporan_individu(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $konsultasiKelompok = $this->buatKonsultasi(
            $guru,
            'Konseling kelompok',
            $this->buatRpl($guru, Rpl::TYPE_KELOMPOK)
        );

        $this->actingAs($admin)
            ->get(route('admin.individual-reports.print', $konsultasiKelompok))
            ->assertNotFound();
    }

    public function test_guru_dapat_membuat_laporan_dari_sesi_individu(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $konsultasi = $this->buatKonsultasi($guru, 'Manajemen waktu belajar');
        $konsultasi->update([
            'result' => 'Catatan hasil dari menu konseling.',
            'evaluation' => 'Catatan evaluasi dari menu konseling.',
        ]);

        $this->actingAs($guru)
            ->get(route('guru.individual-reports.index'))
            ->assertOk()
            ->assertViewHas('availableConsultations', fn ($sessions) => $sessions->contains('id', $konsultasi->id));

        $this->post(route('guru.individual-reports.store'), [
            'consultation_id' => $konsultasi->id,
            'report_title' => 'Laporan manajemen waktu',
            'meeting_number' => 1,
            'duration_minutes' => 45,
            'location' => 'Ruang BK',
            'approach_technique' => 'Refleksi dan konseling individual',
            'semester' => 1,
            'year' => now()->year,
            'result' => 'Siswa menyusun jadwal belajar.',
            'evaluation' => 'Siswa memahami prioritas belajar.',
            'follow_up' => 'Evaluasi jadwal pekan depan.',
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('consultation_requests', [
            'id' => $konsultasi->id,
            'report_title' => 'Laporan manajemen waktu',
            'result' => 'Siswa menyusun jadwal belajar.',
            'evaluation' => 'Siswa memahami prioritas belajar.',
            'status' => ConsultationRequest::STATUS_SELESAI,
        ]);
    }

    public function test_guru_hanya_dapat_memilih_sesi_individu_miliknya(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $guruLain = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $sesiKelompok = $this->buatKonsultasi(
            $guru,
            'Sesi kelompok',
            $this->buatRpl($guru, Rpl::TYPE_KELOMPOK)
        );
        $sesiGuruLain = $this->buatKonsultasi($guruLain, 'Sesi guru lain');

        $this->actingAs($guru)
            ->post(route('guru.individual-reports.store'), [
                'consultation_id' => $sesiKelompok->id,
                'report_title' => 'Tidak boleh dibuat',
                'result' => 'Hasil',
                'evaluation' => 'Evaluasi',
            ])
            ->assertNotFound();

        $this->post(route('guru.individual-reports.store'), [
            'consultation_id' => $sesiGuruLain->id,
            'report_title' => 'Tidak boleh dibuat',
            'result' => 'Hasil',
            'evaluation' => 'Evaluasi',
        ])->assertNotFound();
    }

    public function test_guru_dapat_memperbarui_dan_menghapus_laporan_tanpa_menghapus_sesi(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $konsultasi = $this->buatKonsultasi($guru, 'Sesi konseling');
        $this->isiLaporan($konsultasi, 'Laporan awal');

        $this->actingAs($guru)
            ->put(route('guru.individual-reports.update', $konsultasi), [
                'report_title' => 'Laporan diperbarui',
                'meeting_number' => 2,
                'duration_minutes' => 60,
                'location' => 'Ruang BK',
                'approach_technique' => 'Teknik refleksi',
                'semester' => 2,
                'year' => now()->year,
                'result' => 'Hasil baru',
                'evaluation' => 'Evaluasi baru',
                'follow_up' => 'Tindak lanjut baru',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('consultation_requests', [
            'id' => $konsultasi->id,
            'report_title' => 'Laporan diperbarui',
            'result' => 'Hasil baru',
        ]);

        $this->delete(route('guru.individual-reports.destroy', $konsultasi))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('consultation_requests', [
            'id' => $konsultasi->id,
            'subject' => 'Sesi konseling',
            'report_title' => null,
            'result' => null,
        ]);
    }

    private function buatKonsultasi(User $guru, string $subject, ?Rpl $rpl = null): ConsultationRequest
    {
        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        return ConsultationRequest::query()->create([
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'rpl_id' => $rpl?->id,
            'subject' => $subject,
            'case_category' => ConsultationRequest::CASE_BELAJAR,
            'preferred_time' => '10:00',
            'consultation_date' => now()->toDateString(),
            'status' => ConsultationRequest::STATUS_SELESAI,
        ]);
    }

    private function isiLaporan(ConsultationRequest $consultation, string $title): void
    {
        $consultation->update([
            'report_title' => $title,
            'result' => 'Hasil konseling',
            'evaluation' => 'Evaluasi konseling',
        ]);
    }

    private function buatRpl(User $guru, string $type): Rpl
    {
        return Rpl::query()->create([
            'teacher_id' => $guru->id,
            'title' => 'RPL '.$type,
            'type' => $type,
            'tujuan' => 'Tujuan layanan',
            'materi' => 'Materi layanan',
            'metode' => 'Metode layanan',
            'evaluasi' => 'Evaluasi layanan',
        ]);
    }
}
