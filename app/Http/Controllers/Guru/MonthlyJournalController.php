<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\MonthlyJournal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthlyJournalController extends Controller
{
    public function index(Request $request): View
    {
        $year = $request->integer('year');
        $year = $year > 0 ? $year : null;

        $years = MonthlyJournal::query()
            ->where('teacher_id', auth()->id())
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $journals = MonthlyJournal::query()
            ->where('teacher_id', auth()->id())
            ->when($year, fn ($query) => $query->where('year', $year))
            ->latest('year')
            ->latest('month')
            ->paginate(10)
            ->appends($request->only('year'));

        $groupedJournals = $journals->getCollection()
            ->groupBy('year')
            ->sortKeysDesc();

        return view('guru.journals.index', compact('journals', 'years', 'year', 'groupedJournals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        MonthlyJournal::updateOrCreate(
            [
                'teacher_id' => auth()->id(),
                'month' => $data['month'],
                'year' => $data['year'],
            ],
            $data + ['teacher_id' => auth()->id()]
        );

        return back()->with('success', 'Jurnal bulanan BK berhasil disimpan.');
    }

    public function update(Request $request, MonthlyJournal $journal): RedirectResponse
    {
        abort_unless($journal->teacher_id === auth()->id(), 403);

        $journal->update($this->validatedData($request));

        return back()->with('success', 'Jurnal bulanan BK berhasil diperbarui.');
    }

    public function destroy(MonthlyJournal $journal): RedirectResponse
    {
        abort_unless($journal->teacher_id === auth()->id(), 403);

        $journal->delete();

        return back()->with('success', 'Jurnal bulanan BK berhasil dihapus.');
    }

    public function print(MonthlyJournal $journal)
    {
        abort_unless($journal->teacher_id === auth()->id(), 403);

        $journal->load('teacher.schoolModel');

        return Pdf::loadView('guru.journals.print', compact('journal'))
            ->setPaper('a4')
            ->stream('jurnal-bulanan-bk-'.$journal->month.'-'.$journal->year.'.pdf');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,2100'],
            'title' => ['required', 'string', 'max:255'],
            'individual_services' => ['required', 'integer', 'min:0'],
            'group_services' => ['required', 'integer', 'min:0'],
            'classical_services' => ['required', 'integer', 'min:0'],
            'summary' => ['required', 'string', 'max:5000'],
            'evaluation' => ['nullable', 'string', 'max:5000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
