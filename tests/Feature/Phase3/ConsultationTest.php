<?php

namespace Tests\Feature\Phase3;

use App\Models\ConsultationRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_dapat_melihat_halaman_konseling(): void
    {
        $this->actingAs($this->buatSiswa())
            ->get(route('siswa.consultations.index'))
            ->assertOk()
            ->assertViewIs('siswa.consultations.index');
    }

    public function test_siswa_dapat_mengajukan_konseling(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($siswa)
            ->post(route('siswa.consultations.store'), [
                'counselor_id' => $guru->id,
                'subject' => 'Stres ujian',
                'case_category' => ConsultationRequest::CASE_BELAJAR,
                'preferred_date' => now()->addDays(3)->toDateString(),
                'preferred_time' => '10:00',
                'details' => 'Butuh bimbingan persiapan ujian.',
            ])
            ->assertRedirect(route('siswa.consultations.index'));

        $this->assertDatabaseHas('consultation_requests', [
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'subject' => 'Stres ujian',
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);
    }

    public function test_guru_dapat_melihat_halaman_konseling_dan_events(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.consultations.index'))
            ->assertOk()
            ->assertViewIs('guru.consultations.index');

        $this->actingAs($guru)
            ->getJson(route('guru.consultations.events'))
            ->assertOk();
    }

    public function test_guru_dapat_menyetujui_pengajuan(): void
    {
        [$siswa, $guru, $konseling] = $this->buatPengajuanPending();

        $this->actingAs($guru)
            ->patch(route('guru.consultations.approve', $konseling))
            ->assertRedirect();

        $this->assertDatabaseHas('consultation_requests', [
            'id' => $konseling->id,
            'status' => ConsultationRequest::STATUS_APPROVED,
            'counselor_id' => $guru->id,
        ]);
    }

    public function test_guru_dapat_menolak_pengajuan_dengan_alasan(): void
    {
        [$siswa, $guru, $konseling] = $this->buatPengajuanPending();

        $this->actingAs($guru)
            ->patch(route('guru.consultations.reject', $konseling), [
                'rejection_reason' => 'Jadwal penuh minggu ini.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('consultation_requests', [
            'id' => $konseling->id,
            'status' => ConsultationRequest::STATUS_REJECTED,
            'rejection_reason' => 'Jadwal penuh minggu ini.',
        ]);
    }

    public function test_dashboard_siswa_menampilkan_widget_jadwal_mendatang(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);

        ConsultationRequest::create([
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'subject' => 'Konseling karier',
            'case_category' => ConsultationRequest::CASE_KARIER,
            'preferred_time' => '09:00',
            'consultation_date' => now()->addDay(),
            'consultation_time' => '09:00',
            'status' => ConsultationRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($siswa)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertViewHas('upcoming', fn ($upcoming) => $upcoming->isNotEmpty());
    }

    public function test_dashboard_guru_menampilkan_widget_jadwal_minggu_ini(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);

        ConsultationRequest::create([
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'subject' => 'Konseling belajar',
            'case_category' => ConsultationRequest::CASE_BELAJAR,
            'preferred_time' => '14:00',
            'consultation_date' => now()->addDays(2),
            'consultation_time' => '14:00',
            'status' => ConsultationRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertViewHas('upcomingWeek', fn ($slots) => $slots->isNotEmpty());
    }

    public function test_jadwal_tanpa_jam_tetap_aman_dirender(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
            'name' => 'Guru BK Uji',
        ]);

        $tanggal = now()->addDay()->startOfDay();

        ConsultationRequest::create([
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'subject' => 'Konseling tanpa jam',
            'case_category' => ConsultationRequest::CASE_SOSIAL,
            'preferred_time' => 'Fleksibel',
            'consultation_date' => $tanggal,
            'consultation_time' => null,
            'status' => ConsultationRequest::STATUS_APPROVED,
        ]);

        $guruPage = $this->actingAs($guru)->get(route('guru.consultations.index'));
        $guruPage->assertOk()->assertSee('Konseling tanpa jam');
        $this->assertStringContainsString(
            $tanggal->format('d M').' · Sosial',
            $this->teks($guruPage->getContent()),
            'Widget jadwal guru harus menampilkan satu pemisah, tanpa jam kosong.',
        );

        $siswaPage = $this->actingAs($siswa)->get(route('siswa.consultations.index'));
        $siswaPage->assertOk()->assertSee('Konseling tanpa jam');
        $this->assertStringContainsString(
            $tanggal->format('d M Y').' · '.$guru->name,
            $this->teks($siswaPage->getContent()),
            'Widget jadwal siswa harus menampilkan satu pemisah, tanpa jam kosong.',
        );
    }

    public function test_filter_konseling_menampilkan_tombol_reset(): void
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.consultations.index'))
            ->assertOk()
            ->assertDontSee('>Reset<', escape: false);

        $this->actingAs($guru)
            ->get(route('guru.consultations.index', ['status' => ConsultationRequest::STATUS_APPROVED]))
            ->assertOk()
            ->assertSee('>Reset<', escape: false);

        $this->actingAs($siswa)
            ->get(route('siswa.consultations.index', ['status' => ConsultationRequest::STATUS_PENDING]))
            ->assertOk()
            ->assertSee('>Reset<', escape: false);
    }

    public function test_legacy_feedback_siswa_redirect_ke_penilaian(): void
    {
        $this->actingAs($this->buatSiswa())
            ->get(route('siswa.feedback.create'))
            ->assertRedirect(route('siswa.penilaian.index'));
    }

    public function test_guru_bisa_mencari_konseling_berdasarkan_topik(): void
    {
        [, $guru, $konseling] = $this->buatPengajuanPending();

        $lain = ConsultationRequest::create([
            'student_id' => $konseling->student_id,
            'counselor_id' => $guru->id,
            'subject' => 'Topik yang sangat berbeda',
            'case_category' => ConsultationRequest::CASE_BELAJAR,
            'preferred_time' => 'Pagi',
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);

        $html = $this->teks($this->actingAs($guru)
            ->get(route('guru.consultations.index', ['search' => 'Masalah']))
            ->assertOk()
            ->getContent());

        $this->assertStringContainsString('Masalah sosial', $html);
        $this->assertStringNotContainsString($lain->subject, $html);
    }

    public function test_guru_bisa_mencari_konseling_berdasarkan_nisn_siswa(): void
    {
        [$siswa, $guru] = $this->buatPengajuanPending();

        $nisn = Student::query()->findOrFail($siswa->id)->nisn;

        $html = $this->teks($this->actingAs($guru)
            ->get(route('guru.consultations.index', ['search' => $nisn]))
            ->assertOk()
            ->getContent());

        $this->assertStringContainsString('Masalah sosial', $html);
    }

    public function test_guru_bisa_memfilter_konseling_sesuai_kategori(): void
    {
        [, $guru, $konseling] = $this->buatPengajuanPending();

        $lain = ConsultationRequest::create([
            'student_id' => $konseling->student_id,
            'counselor_id' => $guru->id,
            'subject' => 'Kesulitan belajar',
            'case_category' => ConsultationRequest::CASE_BELAJAR,
            'preferred_time' => 'Pagi',
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);

        $html = $this->teks($this->actingAs($guru)
            ->get(route('guru.consultations.index', ['kategori' => ConsultationRequest::CASE_BELAJAR]))
            ->assertOk()
            ->getContent());

        $this->assertStringContainsString($lain->subject, $html);
        $this->assertStringNotContainsString('Masalah sosial', $html);
    }

    public function test_admin_bisa_mencari_konseling_berdasarkan_nama_siswa(): void
    {
        [$siswa, , $konseling] = $this->buatPengajuanPending();

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);

        $html = $this->teks($this->actingAs($admin)
            ->get(route('admin.consultations.index', ['search' => $siswa->name]))
            ->assertOk()
            ->getContent());

        $this->assertStringContainsString('Masalah sosial', $html);
    }

    public function test_filter_konseling_mencerminkan_filter_kategori_ke_form(): void
    {
        [, $guru] = $this->buatPengajuanPending();

        $this->actingAs($guru)
            ->get(route('guru.consultations.index', ['kategori' => ConsultationRequest::CASE_SOSIAL]))
            ->assertOk()
            ->assertSee('name="kategori"', escape: false)
            ->assertSee('>Reset<', escape: false);
    }

    /**
     * Ratakan HTML supaya spasi/newline dari template tidak mengganggu pencocokan teks.
     */
    private function teks(string $html): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($html)));
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

        return $user;
    }

    /**
     * @return array{0: User, 1: User, 2: ConsultationRequest}
     */
    private function buatPengajuanPending(): array
    {
        $siswa = $this->buatSiswa();
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);

        $konseling = ConsultationRequest::create([
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'subject' => 'Masalah sosial',
            'case_category' => ConsultationRequest::CASE_SOSIAL,
            'preferred_time' => 'Fleksibel',
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);

        return [$siswa, $guru, $konseling];
    }
}
