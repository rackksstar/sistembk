<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\PenilaianPelayanan;
use App\Models\RaporBk;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RaporController extends Controller
{
    public function index(Request $request): View
    {
        $semester = $request->string('semester')->toString();
        $tahunAjaran = $request->string('tahun_ajaran')->toString();
        $status = $request->string('status')->toString();

        $rapor = RaporBk::query()
            ->with([
                'student:id,name,nisn,kelas_id,user_id',
                'student.user:id,name',
                'student.kelas:id,nama',
                'counselor:id,name',
            ])
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->when($tahunAjaran, fn ($q) => $q->where('tahun_ajaran', $tahunAjaran))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.rapor.index', compact('rapor', 'semester', 'tahunAjaran', 'status'));
    }

    public function show(RaporBk $rapor): View
    {
        $rapor->load([
            'student.user',
            'student.kelas',
            'counselor',
        ]);

        return view('admin.rapor.show', compact('rapor'));
    }

    public function exportPdf(RaporBk $rapor): Response
    {
        $rapor->load([
            'student.user',
            'student.kelas',
            'counselor',
        ]);

        $ringkasanKonseling = $this->ringkasanKonseling($rapor->student, (int) $rapor->counselor_id);
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
