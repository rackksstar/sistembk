<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Rpl;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RplController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();

        $rpls = Rpl::query()
            ->where('teacher_id', auth()->id())
            ->when($type, fn ($query) => $query->where('type', $type))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('guru.rpls.index', [
            'rpls' => $rpls,
            'types' => Rpl::TYPES,
            'type' => $type,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Rpl::create($this->validatedData($request) + ['teacher_id' => auth()->id()]);

        return back()->with('success', 'RPL berhasil dibuat.');
    }

    public function update(Request $request, Rpl $rpl): RedirectResponse
    {
        abort_unless($rpl->teacher_id === auth()->id(), 403);

        $rpl->update($this->validatedData($request));

        return back()->with('success', 'RPL berhasil diperbarui.');
    }

    public function destroy(Rpl $rpl): RedirectResponse
    {
        abort_unless($rpl->teacher_id === auth()->id(), 403);

        $rpl->delete();

        return back()->with('success', 'RPL berhasil dihapus.');
    }

    public function print(Rpl $rpl)
    {
        abort_unless($rpl->teacher_id === auth()->id(), 403);

        $rpl->load('teacher.schoolModel');

        return Pdf::loadView('guru.rpls.print', compact('rpl'))
            ->setPaper('a4')
            ->stream('rpl-'.$rpl->id.'.pdf');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Rpl::TYPES))],
            'service_date' => ['nullable', 'date'],
            'target' => ['nullable', 'string', 'max:255'],
            'tujuan' => ['required', 'string', 'max:3000'],
            'materi' => ['required', 'string', 'max:3000'],
            'metode' => ['required', 'string', 'max:3000'],
            'evaluasi' => ['required', 'string', 'max:3000'],
        ]);
    }
}
