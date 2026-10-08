<?php

/**
 * @deprecated Phase 4 (2026-05-30) — Digantikan oleh PenilaianController.
 *
 * Route legacy mengarahkan ke Laporan Penilaian. Tabel service_feedback tidak di-drop.
 *
 * Pengganti: App\Http\Controllers\Guru\PenilaianController
 */

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ServiceFeedbackController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()
            ->route('guru.penilaian.index')
            ->with('info', 'Feedback layanan telah digantikan oleh Laporan Penilaian.');
    }
}
