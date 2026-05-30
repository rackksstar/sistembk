<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\MasterQuestion;
use App\Models\ResponsAngket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AngketController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->studentProfile;
        abort_unless($student, 404);

        $studentId = $student->id;

        $pertanyaan = MasterQuestion::query()
            ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
            ->where('is_active', true)
            ->withCount([
                'responAngket as sudah_dijawab' => function ($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                },
            ])
            ->orderBy('id')
            ->get();

        $totalSoal = $pertanyaan->count();
        $totalDijawab = $pertanyaan->where('sudah_dijawab', 1)->count();
        $sudahSelesai = $totalSoal > 0 && $totalDijawab === $totalSoal;

        return view('siswa.angket.index', compact(
            'pertanyaan', 'totalSoal', 'totalDijawab', 'sudahSelesai'
        ));
    }

    public function show(): View
    {
        $student = auth()->user()->studentProfile;
        abort_unless($student, 404);

        $studentId = $student->id;

        $pertanyaan = MasterQuestion::query()
            ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
            ->where('is_active', true)
            ->with([
                'responAngket' => fn ($q) => $q->where('student_id', $studentId),
            ])
            ->orderBy('id')
            ->get();

        return view('siswa.angket.show', compact('pertanyaan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jawaban' => ['required', 'array', 'min:1'],
            'jawaban.*' => ['required', 'string', 'max:500'],
        ]);

        $student = auth()->user()->studentProfile;
        abort_unless($student, 404);

        $studentId = $student->id;

        $soalIds = array_keys($validated['jawaban']);
        $validSoal = MasterQuestion::query()
            ->whereIn('id', $soalIds)
            ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        DB::transaction(function () use ($validated, $studentId, $validSoal) {
            foreach ($validated['jawaban'] as $soalId => $jawaban) {
                if (! in_array((int) $soalId, $validSoal, true)) {
                    continue;
                }

                ResponsAngket::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'master_question_id' => (int) $soalId,
                    ],
                    ['jawaban' => $jawaban]
                );
            }
        });

        return redirect()
            ->route('siswa.angket.index')
            ->with('success', 'Jawaban angket berhasil disimpan.');
    }
}
