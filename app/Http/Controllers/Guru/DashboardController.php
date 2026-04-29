<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $requests = ConsultationRequest::with('student')
            ->where(function ($query) {
                $query->whereNull('counselor_id')
                    ->orWhere('counselor_id', auth()->id());
            })
            ->latest()
            ->get();

        $metrics = [
            [
                'title' => 'Antrian baru',
                'value' => $requests->where('status', ConsultationRequest::STATUS_MENUNGGU)->count(),
                'description' => 'Permintaan konseling yang belum diproses.',
                'color' => 'from-blue-600 to-sky-400',
            ],
            [
                'title' => 'Dijadwalkan',
                'value' => $requests->where('status', ConsultationRequest::STATUS_DIJADWALKAN)->count(),
                'description' => 'Sesi yang sudah punya jadwal.',
                'color' => 'from-emerald-500 to-teal-400',
            ],
            [
                'title' => 'Selesai',
                'value' => $requests->where('status', ConsultationRequest::STATUS_SELESAI)->count(),
                'description' => 'Sesi konseling yang sudah ditutup.',
                'color' => 'from-violet-500 to-fuchsia-400',
            ],
        ];

        return view('guru.dashboard', compact('metrics', 'requests'));
    }
}
