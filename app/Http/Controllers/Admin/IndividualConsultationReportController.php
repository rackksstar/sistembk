<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\Rpl;
use App\Models\Sekolah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IndividualConsultationReportController extends Controller
{
    public function index(Request $request): View
    {
        return $this->reportIndex($request);
    }

    public function guruIndex(Request $request): View
    {
        return $this->reportIndex($request, (int) auth()->id());
    }

    private function reportIndex(Request $request, ?int $counselorId = null): View
    {
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();
        $kategori = $request->string('case_category')->toString();
        $sekolahId = $request->integer('sekolah_id') ?: null;
        $rplId = $request->integer('rpl_id') ?: null;

        $consultations = $this->individualConsultationQuery()
            ->where(function ($query) {
                $query->whereNotNull('report_title')
                    ->orWhereNotNull('result')
                    ->orWhereNotNull('evaluation');
            })
            ->with([
                'student:id,name,school_id',
                'student.studentProfile:id,user_id,kelas_id,nisn',
                'student.studentProfile.kelas:id,nama,sekolah_id',
                'student.studentProfile.kelas.sekolah:id,nama',
                'counselor:id,name',
                'rpl:id,title,type',
                'programStudi:id,nama,jenjang_pendidikan',
            ])
            ->when($counselorId, fn ($query) => $query->where('counselor_id', $counselorId))
            ->when($rplId, fn ($query) => $query->where('rpl_id', $rplId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($kategori, fn ($query) => $query->where('case_category', $kategori))
            ->when($sekolahId, function ($query) use ($sekolahId) {
                $query->whereHas(
                    'student.studentProfile.kelas',
                    fn ($kelas) => $kelas->where('sekolah_id', $sekolahId)
                );
            })
            ->when($search, fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('subject', 'like', "%{$search}%")
                    ->orWhere('report_title', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($student) => $student
                        ->where('name', 'like', "%{$search}%")
                        ->orWhereHas('studentProfile', fn ($profile) => $profile->where('nisn', 'like', "%{$search}%")))
                    ->orWhereHas('counselor', fn ($counselor) => $counselor->where('name', 'like', "%{$search}%"));
            }))
            ->latest('consultation_date')
            ->paginate(10)
            ->withQueryString();

        $availableConsultations = $counselorId === null
            ? collect()
            : $this->individualConsultationQuery()
                ->where('counselor_id', $counselorId)
                ->whereIn('status', [
                    ConsultationRequest::STATUS_APPROVED,
                    ConsultationRequest::STATUS_RESCHEDULED,
                    ConsultationRequest::STATUS_SELESAI,
                ])
                ->whereNull('report_title')
                ->whereNull('result')
                ->whereNull('evaluation')
                ->with(['student:id,name', 'student.studentProfile:id,user_id,kelas_id', 'student.studentProfile.kelas:id,nama'])
                ->orderByDesc('consultation_date')
                ->orderByDesc('id')
                ->get();

        return view('admin.individual-reports.index', [
            'consultations' => $consultations,
            'availableConsultations' => $availableConsultations,
            'status' => $status,
            'statuses' => ConsultationRequest::filterableStatuses(),
            'caseCategories' => ConsultationRequest::CASE_CATEGORIES,
            'search' => $search,
            'kategori' => $kategori,
            'sekolahId' => $sekolahId,
            'rplId' => $rplId,
            'rplOptions' => Rpl::query()
                ->where('type', Rpl::TYPE_INDIVIDU)
                ->when($counselorId, fn ($query) => $query->where('teacher_id', $counselorId))
                ->orderBy('title')
                ->get(['id', 'title']),
            'sekolahOptions' => Sekolah::query()->orderBy('nama')->get(['id', 'nama']),
            'printRoute' => $counselorId === null
                ? 'admin.individual-reports.print'
                : 'guru.individual-reports.print',
            'isGuru' => $counselorId !== null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request, true);
        $consultation = $this->teacherIndividualConsultation((int) $data['consultation_id']);
        abort_if($this->hasReport($consultation), 422, 'Sesi konseling ini sudah memiliki laporan.');

        unset($data['consultation_id']);
        $consultation->update([
            ...$data,
            'status' => ConsultationRequest::STATUS_SELESAI,
        ]);

        return back()->with('success', 'Laporan konseling individu berhasil dibuat.');
    }

    public function update(Request $request, ConsultationRequest $consultationRequest): RedirectResponse
    {
        abort_unless($consultationRequest->counselor_id === auth()->id(), 403);
        $this->ensureIndividualConsultation($consultationRequest);
        abort_unless($this->hasReport($consultationRequest), 404);

        $consultationRequest->update([
            ...$this->validatedData($request),
            'status' => ConsultationRequest::STATUS_SELESAI,
        ]);

        return back()->with('success', 'Laporan konseling individu berhasil diperbarui.');
    }

    public function destroy(ConsultationRequest $consultationRequest): RedirectResponse
    {
        abort_unless($consultationRequest->counselor_id === auth()->id(), 403);
        $this->ensureIndividualConsultation($consultationRequest);
        abort_unless($this->hasReport($consultationRequest), 404);

        $consultationRequest->update([
            'report_title' => null,
            'approach_technique' => null,
            'result' => null,
            'evaluation' => null,
            'follow_up' => null,
        ]);

        return back()->with('success', 'Laporan individu dihapus; sesi konseling tetap tersimpan.');
    }

    public function guruPrint(ConsultationRequest $consultationRequest)
    {
        abort_unless($consultationRequest->counselor_id === auth()->id(), 403);
        $this->ensureIndividualConsultation($consultationRequest);

        return $this->renderPrint($consultationRequest);
    }

    public function print(ConsultationRequest $consultationRequest)
    {
        $this->ensureIndividualConsultation($consultationRequest);

        return $this->renderPrint($consultationRequest);
    }

    private function individualConsultationQuery(): Builder
    {
        return ConsultationRequest::query()
            ->where(function ($query) {
                $query->whereNull('rpl_id')
                    ->orWhereHas('rpl', fn ($rpl) => $rpl->where('type', Rpl::TYPE_INDIVIDU));
            });
    }

    private function teacherIndividualConsultation(int $consultationId): ConsultationRequest
    {
        return $this->individualConsultationQuery()
            ->where('counselor_id', auth()->id())
            ->whereIn('status', [
                ConsultationRequest::STATUS_APPROVED,
                ConsultationRequest::STATUS_RESCHEDULED,
                ConsultationRequest::STATUS_SELESAI,
            ])
            ->findOrFail($consultationId);
    }

    private function ensureIndividualConsultation(ConsultationRequest $consultationRequest): void
    {
        abort_unless(
            $consultationRequest->rpl_id === null
                || $consultationRequest->rpl()->where('type', Rpl::TYPE_INDIVIDU)->exists(),
            404
        );
    }

    private function hasReport(ConsultationRequest $consultationRequest): bool
    {
        return $consultationRequest->report_title !== null
            || $consultationRequest->result !== null
            || $consultationRequest->evaluation !== null;
    }

    private function validatedData(Request $request, bool $includeConsultation = false): array
    {
        return $request->validate([
            ...($includeConsultation ? [
                'consultation_id' => ['required', 'integer', 'exists:consultation_requests,id'],
            ] : []),
            'report_title' => ['required', 'string', 'max:255'],
            'meeting_number' => ['nullable', 'integer', 'min:1', 'max:100'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'location' => ['nullable', 'string', 'max:255'],
            'approach_technique' => ['nullable', 'string', 'max:2000'],
            'semester' => ['nullable', 'integer', 'in:1,2'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'result' => ['required', 'string', 'max:4000'],
            'evaluation' => ['required', 'string', 'max:4000'],
            'follow_up' => ['nullable', 'string', 'max:4000'],
        ]);
    }

    private function renderPrint(ConsultationRequest $consultationRequest)
    {
        $consultationRequest->load([
            'student.studentProfile.kelas.sekolah',
            'counselor.schoolModel',
            'programStudi',
            'rpl',
        ]);

        return Pdf::loadView('admin.individual-reports.print', compact('consultationRequest'))
            ->setPaper('a4')
            ->stream('laporan-konseling-individu-'.$consultationRequest->id.'.pdf');
    }
}
