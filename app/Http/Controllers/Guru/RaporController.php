<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\PenilaianPelayanan;
use App\Models\RaporBk;
use App\Models\Student;
use App\Services\CounselorStudentService;
use App\Services\RaporBkService;
use App\Support\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RaporController extends Controller
{
    public function __construct(
        private readonly RaporBkService $raporBkService,
        private readonly CounselorStudentService $counselorStudentService
    ) {}

    public function index(Request $request): View
    {
        $semester = $request->string('semester', RaporBk::defaultSemester())->toString();
        $tahunAjaran = $request->string('tahun_ajaran', RaporBk::defaultTahunAjaran())->toString();
        $search = $request->string('search')->toString();

        $counselor = auth()->user();

        $students = $this->counselorStudentService
            ->queryForCounselor($counselor)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->with([
                'user:id,name',
                'kelas:id,nama,sekolah_id',
                'kelas.sekolah:id,nama',
                'raporBk' => fn ($q) => $q
                    ->where('counselor_id', $counselor->id)
                    ->where('semester', $semester)
                    ->where('tahun_ajaran', $tahunAjaran),
            ])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $students->getCollection()->transform(function (Student $student) {
            $student->rapor_periode = $student->raporBk->first();
            $student->status_rapor = $student->rapor_periode?->statusLabel() ?? 'Belum ada';

            return $student;
        });

        return view('guru.rapor.index', compact(
            'students',
            'semester',
            'tahunAjaran',
            'search'
        ));
    }

    public function edit(Request $request, Student $student): View
    {
        abort_unless($this->counselorStudentService->canAccess($student, auth()->user()), 403);

        $semester = $request->string('semester', RaporBk::defaultSemester())->toString();
        $tahunAjaran = $request->string('tahun_ajaran', RaporBk::defaultTahunAjaran())->toString();

        $rapor = RaporBk::query()
            ->where('student_id', $student->id)
            ->where('counselor_id', auth()->id())
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->first();

        $student->load(['user', 'kelas.sekolah']);

        $ringkasanKonseling = $this->ringkasanKonseling($student, auth()->id());

        return view('guru.rapor.edit', compact(
            'student',
            'rapor',
            'semester',
            'tahunAjaran',
            'ringkasanKonseling'
        ));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        abort_unless($this->counselorStudentService->canAccess($student, auth()->user()), 403);

        $validated = $request->validate([
            'semester' => ['required', Rule::in(array_keys(RaporBk::SEMESTERS))],
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'perkembangan_akademik' => ['nullable', 'string', 'max:5000'],
            'perkembangan_sosial' => ['nullable', 'string', 'max:5000'],
            'perkembangan_psikologis' => ['nullable', 'string', 'max:5000'],
            'saran_tindak_lanjut' => ['nullable', 'string', 'max:5000'],
            'catatan_guru' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(RaporBk::STATUSES))],
        ]);

        $rapor = $this->raporBkService->upsertForStudent(
            auth()->id(),
            $student,
            $validated['semester'],
            $validated['tahun_ajaran'],
            $validated
        );

        ActivityLogger::log('rapor_bk.saved', $rapor, [
            'student_id' => $student->id,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('guru.rapor.index', [
                'semester' => $validated['semester'],
                'tahun_ajaran' => $validated['tahun_ajaran'],
            ])
            ->with('success', 'Rapor BK berhasil disimpan.');
    }

    public function exportPdf(RaporBk $rapor): Response
    {
        abort_unless($rapor->counselor_id === auth()->id(), 403);

        $rapor->load([
            'student.user',
            'student.kelas.sekolah',
            'counselor',
        ]);

        $ringkasanKonseling = $this->ringkasanKonseling($rapor->student, $rapor->counselor_id);
        $tanggalCetak = now()->format('d M Y');

        $pdf = Pdf::loadView('guru.rapor.pdf', compact(
            'rapor',
            'ringkasanKonseling',
            'tanggalCetak'
        ))->setPaper('a4', 'portrait');

        $periode = str_replace('/', '-', $rapor->tahun_ajaran);
        $namaFile = 'rapor-bk-'.str($rapor->student->user?->name ?? $rapor->student->name ?? 'siswa')->slug()
            ."-{$rapor->semester}-{$periode}.pdf";

        return $pdf->download($namaFile);
    }

    /**
     * @return array{total_konseling: int, total_dinilai: int, rata_penilaian: float}
     */
    private function ringkasanKonseling(Student $student, int $counselorId): array
    {
        $userId = $student->user_id;

        if (! $userId) {
            return ['total_konseling' => 0, 'total_dinilai' => 0, 'rata_penilaian' => 0.0];
        }

        $totalKonseling = ConsultationRequest::query()
            ->where('student_id', $userId)
            ->where('counselor_id', $counselorId)
            ->where('status', ConsultationRequest::STATUS_SELESAI)
            ->count();

        if ($totalKonseling === 0) {
            return ['total_konseling' => 0, 'total_dinilai' => 0, 'rata_penilaian' => 0.0];
        }

        $stats = PenilaianPelayanan::query()
            ->where('student_id', $student->id)
            ->whereHas('consultationRequest', fn ($q) => $q
                ->where('student_id', $userId)
                ->where('counselor_id', $counselorId)
                ->where('status', ConsultationRequest::STATUS_SELESAI))
            ->selectRaw('COUNT(*) as total_dinilai')
            ->selectRaw('AVG((skor_materi + skor_cara + skor_manfaat) / 3) as rata_penilaian')
            ->first();

        return [
            'total_konseling' => $totalKonseling,
            'total_dinilai' => (int) ($stats->total_dinilai ?? 0),
            'rata_penilaian' => round((float) ($stats->rata_penilaian ?? 0), 1),
        ];
    }
}
