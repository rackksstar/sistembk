<?php

namespace Tests\Feature\Phase6;

use App\Models\GroupConsultationReport;
use App\Models\Rpl;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupConsultationReportFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_dapat_memfilter_laporan_kelompok_dengan_pencarian_status_kategori_rpl_dan_sekolah(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
        $schoolOne = School::query()->create(['name' => 'SMK Filter Satu']);
        $schoolTwo = School::query()->create(['name' => 'SMK Filter Dua']);
        $classOne = $this->buatKelas($schoolOne, 'Kelas Satu');
        $classTwo = $this->buatKelas($schoolTwo, 'Kelas Dua');

        $rplAktif = $this->buatRpl($guru, $classOne, 'RPL Fokus Belajar', Rpl::STATUS_AKTIF);
        $rplSelesai = $this->buatRpl($guru, $classTwo, 'RPL Relasi Sosial', Rpl::STATUS_SELESAI);
        $reportAktif = $this->buatLaporan($guru, $rplAktif, 'REPORT_FILTER_AKTIF', 'belajar');
        $reportSelesai = $this->buatLaporan($guru, $rplSelesai, 'REPORT_FILTER_SELESAI', 'sosial');

        $this->actingAs($guru)
            ->get(route('guru.group-reports.index', ['search' => 'REPORT_FILTER_AKTIF']))
            ->assertOk()
            ->assertSee('REPORT_FILTER_AKTIF')
            ->assertDontSee('REPORT_FILTER_SELESAI');

        $this->get(route('guru.group-reports.index', ['status' => Rpl::STATUS_AKTIF]))
            ->assertOk()
            ->assertSee('REPORT_FILTER_AKTIF')
            ->assertDontSee('REPORT_FILTER_SELESAI');

        $this->get(route('guru.group-reports.index', ['case_category' => 'belajar']))
            ->assertOk()
            ->assertSee('REPORT_FILTER_AKTIF')
            ->assertDontSee('REPORT_FILTER_SELESAI');

        $this->get(route('guru.group-reports.index', ['rpl_id' => $rplSelesai->id]))
            ->assertOk()
            ->assertSee('REPORT_FILTER_SELESAI')
            ->assertDontSee('REPORT_FILTER_AKTIF');

        $this->get(route('guru.group-reports.index', ['school_id' => $schoolOne->id]))
            ->assertOk()
            ->assertSee('REPORT_FILTER_AKTIF')
            ->assertDontSee('REPORT_FILTER_SELESAI');
    }

    private function buatKelas(School $school, string $name): SchoolClass
    {
        return SchoolClass::query()->create([
            'school_id' => $school->id,
            'name' => $name,
            'level' => 'X',
        ]);
    }

    private function buatRpl(User $guru, SchoolClass $class, string $title, string $status): Rpl
    {
        return Rpl::query()->create([
            'teacher_id' => $guru->id,
            'class_id' => $class->id,
            'title' => $title,
            'type' => Rpl::TYPE_KELOMPOK,
            'status' => $status,
            'tujuan' => 'Tujuan layanan',
            'materi' => 'Materi layanan',
            'metode' => 'Metode layanan',
            'evaluasi' => 'Evaluasi layanan',
        ]);
    }

    private function buatLaporan(User $guru, Rpl $rpl, string $title, string $category): GroupConsultationReport
    {
        return GroupConsultationReport::query()->create([
            'teacher_id' => $guru->id,
            'rpl_id' => $rpl->id,
            'class_id' => $rpl->class_id,
            'title' => $title,
            'service_date' => now()->toDateString(),
            'case_category' => $category,
            'result' => 'Hasil laporan',
            'evaluation' => 'Evaluasi laporan',
        ]);
    }
}
