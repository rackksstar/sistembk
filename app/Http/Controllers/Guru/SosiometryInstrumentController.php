<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SosiometryInstrument;
use App\Support\SociometrySchoolScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SosiometryInstrumentController extends Controller
{
    public function index(SociometrySchoolScope $schoolScope): View
    {
        $classes = $schoolScope->classes(auth()->user())
            ->with('students')
            ->get();
        $instruments = SosiometryInstrument::query()
            ->whereIn('kelas_id', $classes->modelKeys())
            ->get()
            ->keyBy('kelas_id');

        return view('guru.sociometry.manage', compact('classes', 'instruments'));
    }

    public function toggle(Request $request, SociometrySchoolScope $schoolScope): RedirectResponse
    {
        $schoolScope->schoolIdOrFail($request->user());
        $validated = $request->validate([
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $kelas = $schoolScope->classes($request->user())->findOrFail($validated['kelas_id']);

        $instrument = SosiometryInstrument::firstOrNew(['kelas_id' => $kelas->id]);
        $instrument->is_active = (bool) $validated['is_active'];
        $instrument->save();

        return back()->with('success', 'Status instrumen sosiometri diperbarui.');
    }
}
