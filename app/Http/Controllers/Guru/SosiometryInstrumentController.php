<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\SosiometryInstrument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SosiometryInstrumentController extends Controller
{
    public function index(): View
    {
        $classes = Kelas::with('students')->get();
        $instruments = SosiometryInstrument::query()->get()->keyBy('kelas_id');

        return view('guru.sociometry.manage', compact('classes', 'instruments'));
    }

    public function toggle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $kelasId = $validated['kelas_id'] ?? null;

        $instrument = SosiometryInstrument::firstOrNew(['kelas_id' => $kelasId]);
        $instrument->is_active = (bool) $validated['is_active'];
        $instrument->save();

        return back()->with('success', 'Status instrumen sosiometri diperbarui.');
    }
}
