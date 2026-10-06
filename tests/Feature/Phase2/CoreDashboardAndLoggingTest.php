<?php

namespace Tests\Feature\Phase2;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreDashboardAndLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_admin_menampilkan_ringkasan_sekolah_aktif(): void
    {
        $admin = $this->buatAdmin();

        Sekolah::create(['nama' => 'SMK Negeri 1', 'npsn' => '1001', 'alamat' => 'Jl. Merdeka', 'is_mou' => true, 'paket_aktif' => 'Tahunan', 'is_active' => true]);
        Sekolah::create(['nama' => 'SMP Negeri 2', 'npsn' => '1002', 'alamat' => 'Jl. Patriot', 'is_mou' => false, 'paket_aktif' => null, 'is_active' => false]);
        Sekolah::create(['nama' => 'SMA Negeri 3', 'npsn' => '1003', 'alamat' => 'Jl.友谊', 'is_mou' => true, 'paket_aktif' => 'Semester', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Sekolah aktif')
            ->assertSee('Paket aktif')
            ->assertSee('Sudah MOU')
            ->assertSee('SMK Negeri 1')
            ->assertViewHas('sekolahStats', fn ($stats) => $stats['total'] === 3
                && $stats['aktif'] === 2
                && $stats['nonaktif'] === 1
                && $stats['mou'] === 2
                && $stats['paket'] === 2)
            ->assertViewHas('sekolahTerbaru', fn ($list) => $list->count() === 3
                && $list->firstWhere('npsn', '1002')?->is_active === false);
    }

    public function test_dashboard_guru_menampilkan_rata_rata_skor_penilaian(): void
    {
        [$guru, $siswa, ] = $this->buatGuruDanSiswaTerhubung();

        $konseling = \App\Models\ConsultationRequest::create([
            'student_id' => $siswa->id,
            'counselor_id' => $guru->id,
            'subject' => 'Evaluasi layanan',
            'case_category' => \App\Models\ConsultationRequest::CASE_SOSIAL,
            'preferred_time' => 'Pagi',
            'consultation_date' => now(),
            'status' => \App\Models\ConsultationRequest::STATUS_SELESAI,
        ]);

        \App\Models\PenilaianPelayanan::create([
            'consultation_request_id' => $konseling->id,
            'student_id' => $siswa->id,
            'skor_materi' => 5,
            'skor_cara' => 5,
            'skor_manfaat' => 5,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee('Rata-rata skor penilaian')
            ->assertSee('Sangat Baik')
            ->assertSee('1 penilaian terkumpul')
            ->assertSee('5.0');
    }

    public function test_dashboard_guru_menampilkan_progres_angket_siswa(): void
    {
        [$guru, $siswa] = $this->buatGuruDanSiswaTerhubung();

        $soal = \App\Models\MasterQuestion::create([
            'teks_pertanyaan' => 'Bagaimana pelayanan konseling?',
            'tipe_input' => 'text',
            'kategori' => \App\Models\MasterQuestion::KATEGORI_ANGKET,
            'is_active' => true,
        ]);

        \App\Models\ResponsAngket::create([
            'student_id' => $siswa->id,
            'master_question_id' => $soal->id,
            'jawaban' => 'Sangat baik',
        ]);

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee('Progres angket siswa')
            ->assertSee('Cakupan pengisian')
            ->assertSee('100%');
    }

    public function test_activity_log_mencatat_crud_sekolah(): void
    {
        $admin = $this->buatAdmin();

        $this->actingAs($admin)
            ->post(route('admin.sekolah.store'), [
                'nama' => 'SMK Nusantara',
                'npsn' => '2001',
                'alamat' => 'Jl. Anggrek',
                'is_mou' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['action' => 'sekolah.created']);

        $sekolah = Sekolah::where('npsn', '2001')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.sekolah.update', $sekolah), [
                'nama' => 'SMK Nusantara 2',
                'npsn' => '2001',
                'alamat' => 'Jl. Anggrek 2',
                'is_mou' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['action' => 'sekolah.updated']);

        $this->actingAs($admin)
            ->delete(route('admin.sekolah.destroy', $sekolah))
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['action' => 'sekolah.deleted']);
        $this->assertDatabaseMissing('sekolahs', ['id' => $sekolah->id]);
    }

    public function test_activity_log_mencatat_crud_kelas(): void
    {
        $admin = $this->buatAdmin();
        $sekolah = Sekolah::create(['nama' => 'SMK Kelas', 'npsn' => '3001', 'alamat' => 'Jl. Kelas', 'is_mou' => true, 'is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.kelas.store'), [
                'sekolah_id' => $sekolah->id,
                'nama' => 'XI RPL 1',
                'jenjang' => 'SMK',
                'tingkatan' => '11',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['action' => 'kelas.created']);

        $kelas = Kelas::where('nama', 'XI RPL 1')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.kelas.destroy', $kelas))
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['action' => 'kelas.deleted']);
    }

    public function test_activity_log_mencatat_crud_kategori_postingan(): void
    {
        $admin = $this->buatAdmin();

        $this->actingAs($admin)
            ->post(route('admin.kategori-postingan.store'), ['name' => 'Tips Belajar'])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['action' => 'kategori-postingan.created']);

        $kategori = \App\Models\PostCategory::where('name', 'Tips Belajar')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.kategori-postingan.destroy', $kategori))
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['action' => 'kategori-postingan.deleted']);
    }

    public function test_validasi_jenjang_kelas_menolak_nilai_di_luar_daftar(): void
    {
        $admin = $this->buatAdmin();
        $sekolah = Sekolah::create(['nama' => 'SMK Validasi', 'npsn' => '4001', 'alamat' => 'Jl. Validasi', 'is_mou' => true, 'is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.kelas.store'), [
                'sekolah_id' => $sekolah->id,
                'nama' => 'X Tata Bus 1',
                'jenjang' => 'UNIVERSITAS',
                'tingkatan' => '11',
            ])
            ->assertSessionHasErrors('jenjang');

        $this->actingAs($admin)
            ->post(route('admin.kelas.store'), [
                'sekolah_id' => $sekolah->id,
                'nama' => 'X Tata Bus 1',
                'jenjang' => 'SMK',
                'tingkatan' => '13',
            ])
            ->assertSessionHasErrors('tingkatan');

        $this->actingAs($admin)
            ->post(route('admin.kelas.store'), [
                'sekolah_id' => $sekolah->id,
                'nama' => 'X Tata Bus 1',
                'jenjang' => 'SMK',
                'tingkatan' => '10',
            ])
            ->assertSessionHasNoErrors();
    }

    private function buatAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
        ]);
    }
}