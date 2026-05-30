<?php

/**
 * @deprecated Phase 4 (2026-05-30) — Digantikan oleh PenilaianController.
 *
 * JANGAN tambahkan fitur baru ke controller ini.
 * Data lama tetap dapat dibaca. Tabel tidak di-drop.
 * Rencana penghapusan: Phase 9.
 *
 * Pengganti: App\Http\Controllers\Guru\PenilaianController
 * Tabel baru: penilaian_pelayanan
 */

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ServiceFeedback;
use Illuminate\View\View;

class ServiceFeedbackController extends Controller
{
    public function index(): View
    {
        $feedback = ServiceFeedback::query()
            ->with(['student:id,name,class_id,school_id', 'student.classModel:id,name', 'student.schoolModel:id,name', 'consultation:id,subject'])
            ->latest()
            ->paginate(12);

        return view('guru.feedback.index', compact('feedback'));
    }
}
