<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\ScheduleConsultationRequest;
use App\Http\Requests\Guru\StoreConsultationReportRequest;
use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $consultations = ConsultationRequest::with(['student.studentProfile', 'counselor'])
            ->where(function ($query) {
                $query->whereNull('counselor_id')->orWhere('counselor_id', auth()->id());
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $students = User::where('role', User::ROLE_SISWA)->where('status', User::STATUS_APPROVED)->orderBy('name')->get();

        return view('guru.consultations.index', compact('consultations', 'students', 'status'));
    }

    public function approve(ConsultationRequest $consultation): RedirectResponse
    {
        $consultation->update([
            'counselor_id' => auth()->id(),
            'status' => ConsultationRequest::STATUS_APPROVED,
        ]);

        return back()->with('success', 'Pengajuan konseling berhasil disetujui.');
    }

    public function schedule(ScheduleConsultationRequest $request, ConsultationRequest $consultation): RedirectResponse
    {
        $data = $request->validated();

        $consultation->update([
            'student_id' => $data['student_id'],
            'counselor_id' => auth()->id(),
            'consultation_date' => $data['consultation_date'],
            'consultation_time' => $data['consultation_time'],
            'notes' => $data['notes'] ?? null,
            'status' => ConsultationRequest::STATUS_APPROVED,
        ]);

        return back()->with('success', 'Jadwal konseling berhasil disimpan.');
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

    public function print(ConsultationRequest $consultation): View
    {
        abort_unless($consultation->counselor_id === auth()->id() || auth()->user()->role === User::ROLE_ADMIN, 403);

        $consultation->load(['student', 'counselor']);

        return view('guru.consultations.print', compact('consultation'));
    }
}
