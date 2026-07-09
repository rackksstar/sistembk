<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
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

        $requests = ConsultationRequest::query()
            ->with(['student:id,name'])
            ->where(fn ($query) => $query
                ->whereNull('counselor_id')
                ->orWhere('counselor_id', $counselorId))
            ->latest()
            ->limit(20)
            ->get();

        $recentStudentHistories = ConsultationRequest::query()
            ->with([
                'student:id,name',
                'student.studentProfile:id,user_id,kelas_id',
                'student.studentProfile.kelas:id,nama',
            ])
            ->where('counselor_id', $counselorId)
            ->where('status', ConsultationRequest::STATUS_SELESAI)
            ->latest('consultation_date')
            ->limit(8)
            ->get();

        $upcomingWeek = ConsultationRequest::query()
            ->with('student:id,name')
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

        return view('guru.dashboard', compact('metrics', 'requests', 'caseStats', 'recentStudentHistories', 'upcomingWeek'));
    }
}
