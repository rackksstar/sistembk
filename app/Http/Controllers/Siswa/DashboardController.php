<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\Postingan;
use App\Models\TryOut;
use App\Models\User;
use App\Support\AuthenticatedStudent;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();
        $studentProfile = AuthenticatedStudent::profile()?->loadMissing(['guidanceClasses', 'kelas.sekolah', 'siswaSmk']);
        $siswaSmk = $studentProfile?->siswaSmk;

        $metrics = [
            [
                'title' => 'Total pengajuan',
                'value' => ConsultationRequest::where('student_id', $userId)->count(),
                'description' => 'Semua permintaan konseling Anda.',
                'color' => 'from-blue-600 to-sky-400',
            ],
            [
                'title' => 'Menunggu',
                'value' => ConsultationRequest::where('student_id', $userId)
                    ->where('status', ConsultationRequest::STATUS_MENUNGGU)
                    ->count(),
                'description' => 'Sedang menunggu respon Guru BK.',
                'color' => 'from-amber-500 to-orange-400',
            ],
            [
                'title' => 'Selesai',
                'value' => ConsultationRequest::where('student_id', $userId)
                    ->where('status', ConsultationRequest::STATUS_SELESAI)
                    ->count(),
                'description' => 'Sesi yang sudah selesai.',
                'color' => 'from-emerald-500 to-teal-400',
            ],
        ];

        $requests = ConsultationRequest::query()
            ->with('counselor:id,name')
            ->where('student_id', $userId)
            ->latest()
            ->limit(10)
            ->get();

        $teachers = User::query()
            ->where('role', User::ROLE_GURU)
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get(['id', 'name']);

        $postinganTerbaru = Postingan::query()
            ->with('kategori:id,name')
            ->where('status', Postingan::STATUS_PUBLISHED)
            ->latest()
            ->take(3)
            ->get();

        $tryoutAktif = collect();
        if ($studentProfile?->kelas_id) {
            $tryoutAktif = TryOut::query()
                ->with(['kelas:id,nama'])
                ->where('status', TryOut::STATUS_AKTIF)
                ->where('mulai_at', '<=', now())
                ->where('selesai_at', '>=', now())
                ->whereHas('kelas', fn ($q) => $q->where('kelas.id', $studentProfile->kelas_id))
                ->latest('mulai_at')
                ->take(3)
                ->get();
        }

        $upcoming = ConsultationRequest::query()
            ->with('counselor:id,name')
            ->where('student_id', $userId)
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

        return view('siswa.dashboard', compact(
            'metrics',
            'requests',
            'teachers',
            'studentProfile',
            'siswaSmk',
            'postinganTerbaru',
            'tryoutAktif',
            'upcoming'
        ));
    }
}
