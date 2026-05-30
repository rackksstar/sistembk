<?php

/**
 * @deprecated Phase 4 (2026-05-30) — Digantikan oleh PenilaianController.
 *
 * JANGAN tambahkan fitur baru ke controller ini.
 * Data lama tetap dapat dibaca. Tabel tidak di-drop.
 * Rencana penghapusan: Phase 9.
 *
 * Pengganti: App\Http\Controllers\Siswa\PenilaianController
 * Tabel baru: penilaian_pelayanan
 */

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\ServiceFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceFeedbackController extends Controller
{
    public function create(): View
    {
        $consultations = ConsultationRequest::query()
            ->where('student_id', auth()->id())
            ->where('status', ConsultationRequest::STATUS_SELESAI)
            ->latest('consultation_date')
            ->get();

        $feedback = ServiceFeedback::query()
            ->where('student_id', auth()->id())
            ->with('consultation:id,subject')
            ->latest()
            ->get();

        return view('siswa.feedback.create', compact('consultations', 'feedback'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'consultation_request_id' => ['nullable', 'exists:consultation_requests,id'],
            'service_type' => ['required', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'message' => ['required', 'string', 'max:3000'],
            'suggestion' => ['nullable', 'string', 'max:3000'],
        ]);

        if (! empty($data['consultation_request_id'])) {
            ConsultationRequest::query()
                ->where('id', $data['consultation_request_id'])
                ->where('student_id', auth()->id())
                ->where('status', ConsultationRequest::STATUS_SELESAI)
                ->exists() || abort(403);
        }

        ServiceFeedback::create($data + ['student_id' => auth()->id()]);

        return back()->with('success', 'Terima kasih, feedback layanan berhasil dikirim.');
    }
}
