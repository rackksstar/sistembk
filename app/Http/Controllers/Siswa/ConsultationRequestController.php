<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsultationRequest;
use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ConsultationRequestController extends Controller
{
    public function store(StoreConsultationRequest $request): RedirectResponse
    {
        $teacher = User::where('role', User::ROLE_GURU)
            ->where('status', User::STATUS_APPROVED)
            ->findOrFail($request->validated('counselor_id'));

        ConsultationRequest::create([
            'student_id' => $request->user()->id,
            'counselor_id' => $teacher->id,
            'subject' => 'Pengajuan konseling siswa',
            'preferred_time' => 'Menunggu jadwal Guru BK',
            'details' => $request->validated('complaint'),
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);

        return back()->with('success', 'Permintaan konseling berhasil dikirim. Guru BK akan segera meninjau pengajuan Anda.');
    }
}
