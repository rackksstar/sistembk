<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\RejectConsultationRequest;
use App\Http\Requests\Guru\ScheduleConsultationRequest;
use App\Http\Requests\Guru\StoreConsultationReportRequest;
use App\Models\ConsultationRequest;
use App\Models\Rpl;
use App\Models\User;
use App\Support\ActivityLogger;
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
        $search = $request->string('search')->toString();
        $kategori = $request->string('kategori')->toString();

        $studentWithKelas = [
            'student:id,name,school',
            'student.studentProfile:id,user_id,kelas_id',
            'student.studentProfile.kelas:id,nama',
            'counselor:id,name',
            'rpl:id,title,class_id,type',
        ];

        $consultations = ConsultationRequest::with($studentWithKelas)
            ->where(function ($query) {
                $query->whereNull('counselor_id')->orWhere('counselor_id', auth()->id());
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($kategori, fn ($query) => $query->where('case_category', $kategori))
            ->when($search, fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('subject', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('student.studentProfile', fn ($sp) => $sp->where('nisn', 'like', "%{$search}%"))
                    ->orWhereHas('counselor', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $upcomingWeek = ConsultationRequest::query()
            ->with($studentWithKelas)
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
            'status' => $status,
            'statuses' => ConsultationRequest::filterableStatuses(),
            'caseCategories' => ConsultationRequest::CASE_CATEGORIES,
            'upcomingWeek' => $upcomingWeek,
            'search' => $search,
            'kategori' => $kategori,
            // Pilihan RPL individu untuk dikaitkan dengan laporan konseling.
            'individualRpls' => Rpl::query()
                ->with('classRoom:id,name')
                ->where('teacher_id', auth()->id())
                ->where('type', Rpl::TYPE_INDIVIDU)
                ->orderByDesc('id')
                ->get(),
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

        ActivityLogger::log('consultation.approved', $consultation);

        return back()->with('success', 'Pengajuan konseling berhasil disetujui.');
    }

    public function reject(RejectConsultationRequest $request, ConsultationRequest $consultation): RedirectResponse
    {
        abort_unless($consultation->canBeRejected(), 422);
        abort_unless($consultation->belongsToCounselor(auth()->id()), 403);

        $consultation->update([
            'counselor_id' => auth()->id(),
            'status' => ConsultationRequest::STATUS_REJECTED,
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        ActivityLogger::log('consultation.rejected', $consultation);

        return back()->with('success', 'Pengajuan konseling ditolak.');
    }

    public function schedule(ScheduleConsultationRequest $request, ConsultationRequest $consultation): RedirectResponse
    {
        abort_unless($consultation->isSchedulable(), 422);
        abort_unless($consultation->belongsToCounselor(auth()->id()), 403);

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

        ActivityLogger::log($hadSchedule ? 'consultation.rescheduled' : 'consultation.scheduled', $consultation);

        return back()->with('success', $message);
    }

    public function report(StoreConsultationReportRequest $request, ConsultationRequest $consultation): RedirectResponse
    {
        abort_unless($consultation->counselor_id === auth()->id(), 403);

        $consultation->update([
            ...$request->validated(),
            'status' => ConsultationRequest::STATUS_SELESAI,
        ]);

        ActivityLogger::log('consultation.completed', $consultation);

        return back()->with('success', 'Laporan konseling berhasil disimpan.');
    }

    public function print(ConsultationRequest $consultation)
    {
        abort_unless($consultation->counselor_id === auth()->id() || auth()->user()->role === User::ROLE_ADMIN, 403);

        $consultation->load([
            'student:id,name,school,class_id',
            'student.studentProfile:id,user_id,kelas_id',
            'student.studentProfile.kelas:id,nama,sekolah_id',
            'student.studentProfile.kelas.sekolah:id,nama',
            'counselor:id,name',
            'rpl:id,title',
        ]);

        $school = $consultation->counselor?->schoolModel
            ?? auth()->user()->schoolModel
            ?? $consultation->student?->studentProfile?->kelas?->sekolah;

        $logoPath = public_path('img/logo_sekolah.png');

        return Pdf::loadView('guru.consultations.print', [
                'consultation' => $consultation,
                'schoolName' => trim((string) ($school?->nama ?? $school?->name ?? $consultation->student?->school ?? '')) ?: 'Nama Sekolah',
                'schoolAddress' => trim((string) ($school?->address ?? '')) ?: '-',
                // Logo dikirim sebagai data URI base64 karena dompdf tidak selalu
                // bisa memuat file lewat URL biasa.
                'logoDataUri' => is_file($logoPath)
                    ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
                    : null,
            ])
            ->setPaper('a4')
            ->stream('laporan-konseling-'.$consultation->id.'.pdf');
    }
}
