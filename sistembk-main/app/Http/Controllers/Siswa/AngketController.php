<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\MasterQuestion;
use App\Models\ResponsAngket;
use App\Support\ActivityLogger;
use App\Support\AngketQuestions;
use App\Support\AuthenticatedStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AngketController extends Controller
{
    public function index(): View
    {
        $student = AuthenticatedStudent::profileOrFail();

        $studentId = $student->id;
        $activeSoalIds = AngketQuestions::activeIds();

        $pertanyaan = MasterQuestion::query()
            ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
            ->where('is_active', true)
            ->when($activeSoalIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $activeSoalIds))
            ->withCount([
                'responAngket as sudah_dijawab' => fn ($q) => $q->where('student_id', $studentId),
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
        $student = AuthenticatedStudent::profileOrFail();

        $studentId = $student->id;
        $activeSoalIds = AngketQuestions::activeIds();

        $jawabanBySoal = ResponsAngket::query()
            ->where('student_id', $studentId)
            ->when($activeSoalIds->isNotEmpty(), fn ($q) => $q->whereIn('master_question_id', $activeSoalIds))
            ->pluck('jawaban', 'master_question_id');

        $pertanyaan = MasterQuestion::query()
            ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
            ->where('is_active', true)
            ->when($activeSoalIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $activeSoalIds))
            ->orderBy('id')
            ->get();

        return view('siswa.angket.show', compact('pertanyaan', 'jawabanBySoal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jawaban' => ['required', 'array', 'min:1'],
            'jawaban.*' => ['required', 'string', 'max:500'],
        ]);

        $student = AuthenticatedStudent::profileOrFail();
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

        $savedCount = collect($validated['jawaban'])
            ->keys()
            ->filter(fn ($soalId) => in_array((int) $soalId, $validSoal, true))
            ->count();

        ActivityLogger::log('angket.submitted', $student, [
            'jumlah_jawaban' => $savedCount,
        ]);

        return redirect()
            ->route('siswa.angket.index')
            ->with('success', 'Jawaban angket berhasil disimpan.');
    }
}
