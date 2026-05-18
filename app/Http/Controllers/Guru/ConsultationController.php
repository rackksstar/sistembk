<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\RejectConsultationRequest;
use App\Http\Requests\Guru\ScheduleConsultationRequest;
use App\Http\Requests\Guru\StoreConsultationReportRequest;
use App\Models\ConsultationRequest;
use App\Models\User;
use App\Services\ConsultationScheduleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function __construct(
        private readonly ConsultationScheduleService $scheduleService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $consultations = ConsultationRequest::with(['student.studentProfile', 'student.schoolModel', 'student.classModel', 'counselor'])
            ->where(function ($query) {
                $query->whereNull('counselor_id')->orWhere('counselor_id', auth()->id());
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $students = User::where('role', User::ROLE_SISWA)->where('status', User::STATUS_APPROVED)->orderBy('name')->get();

        $upcomingWeek = ConsultationRequest::query()
            ->with('student:id,name')
            ->where('counselor_id', auth()->id())
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

        return view('guru.consultations.index', [
            'consultations' => $consultations,
            'students' => $students,
            'status' => $status,
            'statuses' => ConsultationRequest::filterableStatuses(),
            'caseCategories' => ConsultationRequest::CASE_CATEGORIES,
            'upcomingWeek' => $upcomingWeek,
        ]);
    }

    public function events(): JsonResponse
    {
        return response()->json(
            $this->scheduleService->calendarEventsForCounselor((int) auth()->id())->values()
        );
    }

    public function approve(ConsultationRequest $consultation): RedirectResponse
    {
        abort_unless($consultation->status === ConsultationRequest::STATUS_PENDING, 422);

        $consultation->update([
            'counselor_id' => auth()->id(),
            'status' => ConsultationRequest::STATUS_APPROVED,
        ]);

        return back()->with('success', 'Pengajuan konseling berhasil disetujui.');
    }

    public function reject(RejectConsultationRequest $request, ConsultationRequest $consultation): RedirectResponse
    {
        abort_unless($consultation->canBeRejected(), 422);

        $consultation->update([
            'counselor_id' => auth()->id(),
            'status' => ConsultationRequest::STATUS_REJECTED,
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        return back()->with('success', 'Pengajuan konseling ditolak.');
    }

    public function schedule(ScheduleConsultationRequest $request, ConsultationRequest $consultation): RedirectResponse
    {
        abort_unless($consultation->isSchedulable(), 422);

        $data = $request->validated();
        $hadSchedule = $consultation->consultation_date !== null
            && in_array($consultation->status, [
                ConsultationRequest::STATUS_APPROVED,
                ConsultationRequest::STATUS_RESCHEDULED,
            ], true);

        $consultation->update([
            'student_id' => $data['student_id'],
            'counselor_id' => auth()->id(),
            'consultation_date' => $data['consultation_date'],
            'consultation_time' => $data['consultation_time'],
            'notes' => $data['notes'] ?? null,
            'scheduled_at' => $this->scheduleService->scheduledAt($data['consultation_date'], $data['consultation_time']),
            'status' => $hadSchedule
                ? ConsultationRequest::STATUS_RESCHEDULED
                : ConsultationRequest::STATUS_APPROVED,
        ]);

        $message = $hadSchedule
            ? 'Jadwal konseling berhasil diperbarui (dijadwalkan ulang).'
            : 'Jadwal konseling berhasil disimpan.';

        return back()->with('success', $message);
    }

    public function report(StoreConsultationReportRequest $request, ConsultationRequest $consultation): RedirectResponse
    {
        abort_unless($consultation->counselor_id === auth()->id(), 403);

        $consultation->update([
            ...$request->validated(),
            'status' => ConsultationRequest::STATUS_SELESAI,
        ]);

        return back()->with('success', 'Laporan konseling berhasil disimpan.');
    }

    public function print(ConsultationRequest $consultation)
    {
        abort_unless($consultation->counselor_id === auth()->id() || auth()->user()->role === User::ROLE_ADMIN, 403);

        $consultation->load(['student.schoolModel', 'student.classModel', 'counselor']);

        return Pdf::loadView('guru.consultations.print', compact('consultation'))
            ->setPaper('a4')
            ->stream('laporan-konseling-'.$consultation->id.'.pdf');
    }
}
