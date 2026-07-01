<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsultationRequest;
use App\Models\ConsultationRequest;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $consultations = ConsultationRequest::with('counselor')
            ->where('student_id', $request->user()->id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $teachers = User::query()
            ->where('role', User::ROLE_GURU)
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get(['id', 'name']);

        $upcoming = ConsultationRequest::query()
            ->with('counselor:id,name')
            ->where('student_id', $request->user()->id)
            ->whereIn('status', [
                ConsultationRequest::STATUS_APPROVED,
                ConsultationRequest::STATUS_RESCHEDULED,
            ])
            ->whereNotNull('consultation_date')
            ->where('consultation_date', '>=', now()->startOfDay())
            ->orderBy('consultation_date')
            ->orderBy('consultation_time')
            ->limit(3)
            ->get();

        return view('siswa.consultations.index', [
            'consultations' => $consultations,
            'teachers' => $teachers,
            'status' => $status,
            'statuses' => ConsultationRequest::filterableStatuses(),
            'caseCategories' => ConsultationRequest::CASE_CATEGORIES,
            'upcoming' => $upcoming,
        ]);
    }

    public function store(StoreConsultationRequest $request): RedirectResponse
    {
        User::where('role', User::ROLE_GURU)
            ->where('status', User::STATUS_APPROVED)
            ->findOrFail($request->validated('counselor_id'));

        $preferredDate = $request->validated('preferred_date');
        $preferredTime = $request->validated('preferred_time');

        $created = ConsultationRequest::create([
            'student_id' => $request->user()->id,
            'counselor_id' => $request->validated('counselor_id'),
            'subject' => $request->validated('subject'),
            'case_category' => $request->validated('case_category'),
            'preferred_date' => $preferredDate,
            'preferred_time' => $preferredTime
                ?: ($preferredDate ? 'Sesuai tanggal pilihan' : 'Fleksibel — menunggu jadwal Guru BK'),
            'details' => $request->validated('details'),
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);

        ActivityLogger::log('consultation.submitted', $created);

        return redirect()
            ->route('siswa.consultations.index')
            ->with('success', 'Pengajuan konseling berhasil dikirim. Guru BK akan meninjau permintaan Anda.');
    }
}
