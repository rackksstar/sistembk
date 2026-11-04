<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\MonthlyJournal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
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
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->only('year'));

        $groupedJournals = $journals->getCollection()
            ->groupBy('year')
            ->sortKeysDesc();

        return view('guru.journals.index', compact('journals', 'years', 'year', 'groupedJournals'));
    }

    /**
     * Satu entri layanan = satu baris baru. month/year ikut dihitung dari
     * entry_date sehingga satu bulan boleh memuat banyak entri.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        MonthlyJournal::create($data + ['teacher_id' => auth()->id()]);

        return back()->with('success', 'Entri jurnal BK berhasil disimpan.');
    }

    public function update(Request $request, MonthlyJournal $journal): RedirectResponse
    {
        abort_unless($journal->teacher_id === auth()->id(), 403);

        $journal->update($this->validatedData($request));

        return back()->with('success', 'Entri jurnal BK berhasil diperbarui.');
    }

    public function destroy(MonthlyJournal $journal): RedirectResponse
    {
        abort_unless($journal->teacher_id === auth()->id(), 403);

        $journal->delete();

        return back()->with('success', 'Entri jurnal BK berhasil dihapus.');
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
        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'service_type' => ['required', Rule::in(array_keys(MonthlyJournal::SERVICE_TYPES))],
            'case_category' => ['required', Rule::in(array_keys(\App\Models\ConsultationRequest::CASE_CATEGORIES))],
            'target_type' => ['required', Rule::in(array_keys(MonthlyJournal::TARGET_TYPES))],
            'target_name' => ['required', 'string', 'max:255'],
            'individual_services' => ['required', 'integer', 'min:0'],
            'group_services' => ['required', 'integer', 'min:0'],
            'classical_services' => ['required', 'integer', 'min:0'],
            'summary' => ['required', 'string', 'max:5000'],
            'outcome' => ['nullable', 'string', 'max:5000'],
            'evaluation' => ['nullable', 'string', 'max:5000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
        ]);

        $entryDate = Carbon::parse($validated['entry_date']);
        $validated['month'] = $entryDate->month;
        $validated['year'] = $entryDate->year;

        return $validated;
    }
}
