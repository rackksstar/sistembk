<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\GuruBk;
use App\Models\MasterQuestion;
use App\Models\ResponsAngket;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $counselorId = (int) auth()->id();

        $baseQuery = ConsultationRequest::query()
            ->where(fn ($query) => $query
                ->whereNull('counselor_id')
                ->orWhere('counselor_id', $counselorId));

        $metrics = [
            [
                'title' => 'Antrian baru',
                'value' => (clone $baseQuery)->where('status', ConsultationRequest::STATUS_MENUNGGU)->count(),
                'description' => 'Permintaan konseling yang belum diproses.',
                'color' => 'from-blue-600 to-sky-400',
            ],
            [
                'title' => 'Dijadwalkan',
                'value' => (clone $baseQuery)->whereIn('status', [
                    ConsultationRequest::STATUS_DIJADWALKAN,
                    ConsultationRequest::STATUS_RESCHEDULED,
                ])->count(),
                'description' => 'Sesi yang sudah punya jadwal.',
                'color' => 'from-emerald-500 to-teal-400',
            ],
            [
                'title' => 'Selesai',
                'value' => (clone $baseQuery)->where('status', ConsultationRequest::STATUS_SELESAI)->count(),
                'description' => 'Sesi konseling yang sudah ditutup.',
                'color' => 'from-violet-500 to-fuchsia-400',
            ],
        ];

        $caseStats = ConsultationRequest::query()
            ->where('counselor_id', $counselorId)
            ->whereNotNull('case_category')
            ->selectRaw('case_category, count(*) as total')
            ->groupBy('case_category')
            ->pluck('total', 'case_category');

        $studentWithKelas = [
            'student:id,name',
            'student.studentProfile:id,user_id,kelas_id',
            'student.studentProfile.kelas:id,nama',
        ];

        $requests = ConsultationRequest::query()
            ->with($studentWithKelas)
            ->where(fn ($query) => $query
                ->whereNull('counselor_id')
                ->orWhere('counselor_id', $counselorId))
            ->whereIn('status', [
                ConsultationRequest::STATUS_PENDING,
                ConsultationRequest::STATUS_APPROVED,
                ConsultationRequest::STATUS_RESCHEDULED,
            ])
            ->latest()
            ->limit(20)
            ->get();

        $recentStudentHistories = ConsultationRequest::query()
            ->with($studentWithKelas)
            ->where('counselor_id', $counselorId)
            ->where('status', ConsultationRequest::STATUS_SELESAI)
            ->latest('consultation_date')
            ->limit(8)
            ->get();

        $upcomingWeek = ConsultationRequest::query()
            ->with($studentWithKelas)
            ->where('counselor_id', $counselorId)
            ->whereIn('status', [
                ConsultationRequest::STATUS_APPROVED,
                ConsultationRequest::STATUS_RESCHEDULED,
            ])
            ->whereNotNull('consultation_date')
            ->whereBetween('consultation_date', [now()->startOfDay(), now()->addDays(7)])
            ->orderBy('consultation_date')
            ->orderBy('consultation_time')
            ->limit(5)
            ->get();

        $penilaianAggregate = $this->penilaianAggregate($counselorId);

        $angketAggregate = $this->angketAggregate($counselorId);

        return view('guru.dashboard', compact(
            'metrics', 'requests', 'caseStats',
            'recentStudentHistories', 'upcomingWeek',
            'penilaianAggregate', 'angketAggregate'
        ));
    }

    /**
     * Rata-rata skor penilaian pelayanan milik Guru BK ini.
     *
     * @return array<string, mixed>
     */
    private function penilaianAggregate(int $counselorId): array
    {
        $base = fn () => ConsultationRequest::query()
            ->where('counselor_id', $counselorId)
            ->whereHas('penilaianPelayanan');

        $total = (clone $base())->count();

        $rata = (clone $base())
            ->join('penilaian_pelayanan', 'penilaian_pelayanan.consultation_request_id', '=', 'consultation_requests.id')
            ->selectRaw('
                avg(penilaian_pelayanan.skor_materi) as materi,
                avg(penilaian_pelayanan.skor_cara) as cara,
                avg(penilaian_pelayanan.skor_manfaat) as manfaat
            ')
            ->first();

        $materi = round((float) ($rata?->materi ?? 0), 1);
        $cara = round((float) ($rata?->cara ?? 0), 1);
        $rataManfaat = round((float) ($rata?->manfaat ?? 0), 1);
        $overall = $total > 0 ? round(($materi + $cara + $rataManfaat) / 3, 1) : 0.0;

        return [
            'total' => $total,
            'materi' => $materi,
            'cara' => $cara,
            'manfaat' => $rataManfaat,
            'overall' => $overall,
            'persen' => [
                'materi' => $this->persen($materi),
                'cara' => $this->persen($cara),
                'manfaat' => $this->persen($rataManfaat),
                'overall' => $this->persen($overall),
            ],
            'predikat' => match (true) {
                $overall >= 4.5 => 'Sangat Baik',
                $overall >= 3.5 => 'Baik',
                $overall >= 2.5 => 'Cukup',
                default => 'Perlu Perbaikan',
            },
            'belum' => ConsultationRequest::query()
                ->where('counselor_id', $counselorId)
                ->where('status', ConsultationRequest::STATUS_SELESAI)
                ->whereDoesntHave('penilaianPelayanan')
                ->count(),
        ];
    }

    /**
     * Progres angket siswa yang berada dalam cakupan Guru BK ini.
     *
     * @return array<string, int>
     */
    private function angketAggregate(int $counselorId): array
    {
        $totalSoal = MasterQuestion::query()
            ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
            ->where('is_active', true)
            ->count();

        $studentsQuery = Student::query()
            ->select('students.id')
            ->whereHas('kelas.sekolah', fn ($q) => $q->whereIn(
                'sekolah_id',
                GuruBk::query()->where('user_id', $counselorId)->select('sekolah_id')
            ));

        $jumlahSiswa = (clone $studentsQuery)->count();

        $sudah = $totalSoal === 0
            ? 0
            : ResponsAngket::query()
                ->whereIn('student_id', $studentsQuery)
                ->whereIn('master_question_id', MasterQuestion::query()
                    ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
                    ->where('is_active', true)
                    ->select('id'))
                ->distinct()
                ->count('student_id');

        return [
            'total_soal' => $totalSoal,
            'siswa' => $jumlahSiswa,
            'sudah' => $sudah,
            'belum' => max(0, $jumlahSiswa - $sudah),
            'persen' => $jumlahSiswa > 0 ? (int) round($sudah / $jumlahSiswa * 100) : 0,
        ];
    }

    /**
     * Ubah skor skala 1-5 menjadi lebar bar 0-100 persen.
     */
    private function persen(float $skor): float
    {
        return min(100, max(0, round($skor / 5 * 100, 1)));
    }
}
