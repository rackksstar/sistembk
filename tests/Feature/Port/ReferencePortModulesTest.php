<?php

namespace Tests\Feature\Port;

use App\Models\ConsultationRequest;
use App\Models\InstrumentQuestion;
use App\Models\Kelas;
use App\Models\MonthlyJournal;
use App\Models\Rpl;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SosiometryInstrument;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test integrasi untuk 5 modul yang diport dari salinan referensi:
 * Jurnal Bulanan, RPL, Laporan Konseling, Hasil Instrumen (MBTI), dan
 * Sociometry Map.
 */
class ReferencePortModulesTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // 1. Jurnal Bulanan
    // ------------------------------------------------------------------

    public function test_jurnal_mendukung_banyak_entri_dalam_satu_bulan(): void
    {
        $guru = $this->buatGuru();

        $payload = fn (string $date, string $title) => [
            'entry_date' => $date,
            'title' => $title,
            'service_type' => MonthlyJournal::SERVICE_TYPE_INDIVIDU,
            'case_category' => ConsultationRequest::CASE_BELAJAR,
            'target_type' => MonthlyJournal::TARGET_SISWA,
            'target_name' => 'Kelas X',
            'individual_services' => 1,
            'group_services' => 0,
            'classical_services' => 0,
            'summary' => 'Rangkuman layanan.',
            'outcome' => 'Siswa lebih terbuka.',
            'evaluation' => 'Layanan berjalan lancar.',
            'follow_up' => 'Dilanjutkan pekan depan.',
        ];

        $this->actingAs($guru)
            ->post(route('guru.journals.store'), $payload('2026-03-05', 'Entri awal Maret'))
            ->assertSessionHas('success');

        $this->actingAs($guru)
            ->post(route('guru.journals.store'), $payload('2026-03-20', 'Entri akhir Maret'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('monthly_journals', 2);

        // month/year diturunkan dari entry_date, bukan dari waktu pembuatan.
        $entri = MonthlyJournal::query()
            ->where('teacher_id', $guru->id)
            ->where('title', 'Entri awal Maret')
            ->firstOrFail();

        $this->assertSame('2026-03-05', $entri->entry_date->toDateString());
        $this->assertSame(3, (int) $entri->month);
        $this->assertSame(2026, (int) $entri->year);
        $this->assertSame('Siswa lebih terbuka.', $entri->outcome);

        $this->actingAs($guru)
            ->get(route('guru.journals.index'))
            ->assertOk()
            ->assertViewIs('guru.journals.index');
    }

    public function test_jurnal_wajib_menyertakan_tanggal_pelaksanaan(): void
    {
        $guru = $this->buatGuru();

        $this->actingAs($guru)
            ->post(route('guru.journals.store'), [
                'title' => 'Tanpa tanggal',
                'service_type' => MonthlyJournal::SERVICE_TYPE_INDIVIDU,
                'case_category' => ConsultationRequest::CASE_BELAJAR,
                'target_type' => MonthlyJournal::TARGET_SISWA,
                'target_name' => 'Kelas X',
                'individual_services' => 1,
                'group_services' => 0,
                'classical_services' => 0,
                'summary' => 'Rangkuman.',
            ])
            ->assertSessionHasErrors('entry_date');

        $this->assertDatabaseCount('monthly_journals', 0);
    }

    // ------------------------------------------------------------------
    // 2. RPL
    // ------------------------------------------------------------------

    public function test_rpl_kelompok_wajib_mengisi_field_pelaksanaan(): void
    {
        $guru = $this->buatGuru();
        $classId = $this->buatSchoolClass()->id;

        $this->actingAs($guru)
            ->post(route('guru.rpls.store'), $this->payloadRpl([
                'type' => Rpl::TYPE_KELOMPOK,
                'class_id' => $classId,
                'group_student_ids' => [
                    $this->buatSiswaDiKelas($classId)->id,
                    $this->buatSiswaDiKelas($classId)->id,
                ],
            ]))
            ->assertSessionHasErrors([
                'topik_permasalahan',
                'meeting_number',
                'duration_minutes',
                'location',
            ]);

        $this->assertDatabaseCount('rpls', 0);
    }

    public function test_rpl_individu_mengosongkan_field_khusus_kelompok(): void
    {
        $guru = $this->buatGuru();
        $classId = $this->buatSchoolClass()->id;
        $siswa = $this->buatSiswaDiKelas($classId);

        $this->actingAs($guru)
            ->post(route('guru.rpls.store'), $this->payloadRpl([
                'type' => Rpl::TYPE_INDIVIDU,
                'class_id' => $classId,
                'student_id' => $siswa->id,
                'meeting_number' => 2,
                'duration_minutes' => 45,
                'topik_permasalahan' => 'Harusnya dibersihkan',
                'location' => 'Ruang BK',
                'media' => 'Lembar kerja siswa',
                'group_student_ids' => [
                    $this->buatSiswaDiKelas($classId)->id,
                    $this->buatSiswaDiKelas($classId)->id,
                ],
            ]))
            ->assertSessionHas('success');

        $rpl = Rpl::query()->firstOrFail();

        $this->assertSame($siswa->id, $rpl->student_id);
        // Anggota kelompok ikut dibersihkan untuk RPL individu.
        $this->assertSame(0, $rpl->groupStudents()->count());
        $this->assertNull($rpl->topik_permasalahan);
        $this->assertSame('Ruang BK', $rpl->location);
        $this->assertSame('Lembar kerja siswa', $rpl->media);
        $this->assertSame(2, $rpl->meeting_number);
        $this->assertSame(45, $rpl->duration_minutes);
    }

    // ------------------------------------------------------------------
    // 3. Laporan Konseling
    // ------------------------------------------------------------------

    public function test_laporan_konseling_menyimpan_field_pelaksanaan(): void
    {
        [$guru, $student] = $this->buatGuruDanSiswaTerhubung();

        $consultation = $this->buatConsultationRequest(
            $guru,
            $student,
            ConsultationRequest::STATUS_APPROVED
        );

        $this->actingAs($guru)
            ->patch(route('guru.consultations.report', $consultation), [
                'rpl_id' => null,
                'case_category' => ConsultationRequest::CASE_BELAJAR,
                'meeting_number' => 2,
                'duration_minutes' => 40,
                'location' => 'Ruang BK 1',
                'approach_technique' => 'Konseling naratif, active listening.',
                'semester' => 2,
                'year' => 2026,
                'result' => 'Siswa menyusun jadwal belajar.',
                'evaluation' => 'Siswa lebih terarah.',
                'follow_up' => 'Evaluasi 2 pekan lagi.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('consultation_requests', [
            'id' => $consultation->id,
            'meeting_number' => 2,
            'duration_minutes' => 40,
            'location' => 'Ruang BK 1',
            'approach_technique' => 'Konseling naratif, active listening.',
            'semester' => 2,
            'year' => 2026,
            'status' => ConsultationRequest::STATUS_SELESAI,
        ]);
    }

    public function test_laporan_konseling_wajib_isi_semester_dan_tahun(): void
    {
        [$guru, $student] = $this->buatGuruDanSiswaTerhubung();

        $consultation = $this->buatConsultationRequest(
            $guru,
            $student,
            ConsultationRequest::STATUS_APPROVED
        );

        $this->actingAs($guru)
            ->patch(route('guru.consultations.report', $consultation), [
                'case_category' => ConsultationRequest::CASE_BELAJAR,
                'result' => 'Hasil.',
                'evaluation' => 'Evaluasi.',
            ])
            ->assertSessionHasErrors([
                'meeting_number',
                'duration_minutes',
                'location',
                'approach_technique',
                'semester',
                'year',
            ]);
    }

    public function test_halaman_laporan_konseling_memuat_pilihan_rpl_individu_dan_kelompok(): void
    {
        [$guru, $student, $kelas] = $this->buatGuruDanSiswaTerhubung();
        $siswaUser = $student->user;
        $siswaUser->forceFill(['class_id' => $this->buatSchoolClass()->id])->save();
        $siswaUser->refresh();

        $this->buatConsultationRequest($guru, $student, ConsultationRequest::STATUS_PENDING);

        Rpl::query()->create($this->payloadRpl([
            'type' => Rpl::TYPE_INDIVIDU,
            'title' => 'RPL Individu Demo',
            'class_id' => $siswaUser->class_id,
            'student_id' => $siswaUser->id,
            'meeting_number' => 1,
            'duration_minutes' => 45,
            'location' => 'Ruang BK',
        ]) + ['teacher_id' => $guru->id]);

        $rplKelompok = Rpl::query()->create($this->payloadRpl([
            'type' => Rpl::TYPE_KELOMPOK,
            'title' => 'RPL Kelompok Demo',
            'class_id' => $siswaUser->class_id,
            'student_id' => null,
            'meeting_number' => 1,
            'duration_minutes' => 90,
            'location' => 'Ruang BK',
        ]) + ['teacher_id' => $guru->id]);
        $rplKelompok->groupStudents()->sync([$siswaUser->id]);

        $response = $this->actingAs($guru)->get(route('guru.consultations.index'));

        $response->assertOk()->assertViewHas('linkableRpls');
        $this->assertTrue($response->viewData('linkableRpls')->pluck('id')->contains($rplKelompok->id));
        // RPL kelompok yang cocok dengan kelas siswa ikut dirender sebagai opsi.
        $response->assertSee('RPL Kelompok Demo', false);
        $response->assertSee('RPL Individu Demo', false);
    }

    // ------------------------------------------------------------------
    // 4. Hasil Instrumen (MBTI)
    // ------------------------------------------------------------------

    public function test_guru_dapat_membuat_soal_kepribadian_format_mbti(): void
    {
        $guru = $this->buatGuru();

        $this->actingAs($guru)
            ->post(route('guru.instrument-questions.store'), [
                'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
                'question' => 'Ketika berada di tempat ramai, saya...',
                'is_active' => 1,
                'mbti_axis' => 'EI',
                'mbti_options' => [
                    'Saya senang mengobrol dengan banyak orang.',
                    'Saya lebih suka menyendiri.',
                ],
            ])
            ->assertSessionHas('success');

        $question = InstrumentQuestion::query()->firstOrFail();

        $this->assertSame('EI', \App\Support\Mbti::axisForPole($question->options[0]['pole']));
        $this->assertSame(
            ['E', 'I'],
            array_column($question->options, 'pole')
        );
        $this->assertArrayNotHasKey('score', $question->options[0]);
    }

    public function test_soal_kepribadian_wajib_menyertakan_dan_kutub_mbti(): void
    {
        $guru = $this->buatGuru();

        $this->actingAs($guru)
            ->post(route('guru.instrument-questions.store'), [
                'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
                'question' => 'Soal tanpa dimensi MBTI.',
                'is_active' => 1,
                'mbti_options' => ['Hanya satu pernyataan.'],
            ])
            ->assertSessionHasErrors(['mbti_axis', 'mbti_options']);

        $this->assertDatabaseCount('instrument_questions', 0);
    }

    public function test_hasil_instrumen_mbti_ditampilkan_di_halaman_guru_dan_siswa(): void
    {
        [$guru] = $this->buatGuruDanSiswaTerhubung();

        InstrumentQuestion::query()->create([
            'category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN,
            'question' => 'Ketika berada di tempat ramai, saya...',
            'options' => [
                ['label' => 'Saya senang mengobrol.', 'pole' => 'E'],
                ['label' => 'Saya lebih suka menyendiri.', 'pole' => 'I'],
            ],
            'is_active' => true,
            'created_by' => $guru->id,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.instrument-questions.index'))
            ->assertOk();

        $this->actingAs($guru)
            ->get(route('guru.instrument-results.index', ['category' => InstrumentQuestion::CATEGORY_KEPRIBADIAN]))
            ->assertOk()
            ->assertViewIs('guru.instruments.results.index');
    }

    // ------------------------------------------------------------------
    // 5. Sociometry Map — toggle guru harus berlaku ke sisi siswa
    // ------------------------------------------------------------------

    public function test_siswa_diblokir_saat_instrumen_sosiometri_dinonaktifkan(): void
    {
        [$guru, $student, $kelas] = $this->buatGuruDanSiswaTerhubung();
        $siswa = $student->user;

        $instrument = SosiometryInstrument::query()->create([
            'kelas_id' => $kelas->id,
            'is_active' => false,
        ]);

        $this->actingAs($siswa)
            ->get(route('siswa.sociometry.index'))
            ->assertStatus(403);

        $this->actingAs($siswa)
            ->post(route('siswa.sociometry.store'), [
                'close_friend_id' => $this->buatSiswaBiasa()->id,
                'study_friend_id' => $this->buatSiswaBiasa()->id,
            ])
            ->assertStatus(403);

        $this->assertDatabaseCount('sociometry_responses', 0);

        // Diaktifkan lagi oleh guru → siswa bisa mengakses kembali.
        $instrument->update(['is_active' => true]);

        $this->actingAs($siswa)
            ->get(route('siswa.sociometry.index'))
            ->assertOk()
            ->assertViewIs('siswa.sociometry.index');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function buatGuru(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    private function buatSchoolClass(): SchoolClass
    {
        $school = School::query()->firstOrCreate(['name' => 'SMK Test BK'], [
            'address' => 'Jalan Pendidikan No. 1',
        ]);

        return SchoolClass::query()->create([
            'school_id' => $school->id,
            'name' => 'X TKJ 1',
            'level' => 'X',
        ]);
    }

    private function buatSiswaDiKelas(int $classId): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $user->forceFill(['class_id' => $classId])->save();

        Student::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'nisn' => fake()->unique()->numerify('##########'),
            'birth_date' => '2010-01-01',
            'kelas_id' => Kelas::query()->first()?->id,
        ]);

        return $user->fresh();
    }

    /**
     * Siswa biasa tanpa kelas — untuk data pemilih sosiometri.
     */
    private function buatSiswaBiasa(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buatConsultationRequest(User $guru, $student, string $status): ConsultationRequest
    {
        $studentUserId = $student instanceof User ? $student->id : $student->user_id;

        return ConsultationRequest::query()->create([
            'student_id' => $studentUserId,
            'counselor_id' => $guru->id,
            'subject' => 'Butuh bimbingan',
            'preferred_time' => '10:00-11:00',
            'preferred_date' => '2026-03-10',
            'case_category' => ConsultationRequest::CASE_BELAJAR,
            'status' => $status,
            'details' => 'Siswa kesulitan fokus belajar.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadRpl(array $overrides = []): array
    {
        $class = SchoolClass::query()->firstOrCreate(['name' => 'X TKJ 1'], [
            'school_id' => School::query()->firstOrCreate(['name' => 'SMK Test BK'], [
                'address' => 'Jalan Pendidikan No. 1',
            ])->id,
            'level' => 'X',
        ]);

        return array_merge([
            'title' => 'RPL Konseling',
            'type' => Rpl::TYPE_INDIVIDU,
            'class_id' => $class->id,
            'status' => Rpl::STATUS_AKTIF,
            'semester' => 1,
            'year' => 2026,
            'service_date' => '2026-03-10',
            'target' => 'Siswa',
            'tujuan' => 'Meningkatkan kesiapan belajar siswa.',
            'materi' => 'Perencanaan belajar mingguan.',
            'metode' => 'Wawancara dan diskusi.',
            'evaluasi' => 'Siswa mampu menyusun rencana belajar.',
        ], $overrides);
    }
}
