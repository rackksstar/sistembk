<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $requests = ConsultationRequest::with('counselor')
            ->where('student_id', auth()->id())
            ->latest()
            ->get();
        $teachers = User::where('role', User::ROLE_GURU)
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get();
        $studentProfile = auth()->user()->studentProfile()->with('guidanceClasses')->first();

        $metrics = [
            [
                'title' => 'Total pengajuan',
                'value' => $requests->count(),
                'description' => 'Semua permintaan konseling Anda.',
                'color' => 'from-blue-600 to-sky-400',
            ],
            [
                'title' => 'Menunggu',
                'value' => $requests->where('status', ConsultationRequest::STATUS_MENUNGGU)->count(),
                'description' => 'Sedang menunggu respon Guru BK.',
                'color' => 'from-amber-500 to-orange-400',
            ],
            [
                'title' => 'Selesai',
                'value' => $requests->where('status', ConsultationRequest::STATUS_SELESAI)->count(),
                'description' => 'Sesi yang sudah selesai.',
                'color' => 'from-emerald-500 to-teal-400',
            ],
        ];

        return view('siswa.dashboard', compact('metrics', 'requests', 'teachers', 'studentProfile'));
    }
}
