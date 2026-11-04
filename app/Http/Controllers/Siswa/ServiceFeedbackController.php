<?php

/**
 * @deprecated Phase 4 (2026-05-30) — Digantikan oleh PenilaianController.
 *
 * Route legacy mengarahkan ke Penilaian Layanan. Tabel service_feedback tidak di-drop.
 *
 * Pengganti: App\Http\Controllers\Siswa\PenilaianController
 */

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceFeedbackController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()
            ->route('siswa.penilaian.index')
            ->with('info', 'Feedback layanan telah digantikan oleh Penilaian Layanan (3 skor).');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()
            ->route('siswa.penilaian.index')
            ->with('info', 'Feedback layanan telah digantikan oleh Penilaian Layanan (3 skor).');
    }
}
