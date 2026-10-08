<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\TryOut;
use App\Models\TryOutDetail;
use App\Services\TryOutService;
use App\Support\ActivityLogger;
use App\Support\AuthenticatedStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TryoutController extends Controller
{
    public function __construct(
        private readonly TryOutService $tryOutService
    ) {}

    public function index(): View
    {
        $student = AuthenticatedStudent::profileOrFail();

        $tryouts = collect();
        if ($student->kelas_id) {
            $tryouts = TryOut::query()
                ->with(['kelas:id,nama'])
                ->where('status', TryOut::STATUS_AKTIF)
                ->whereHas('kelas', fn ($q) => $q->where('kelas.id', $student->kelas_id))
                ->where('mulai_at', '<=', now())
                ->where('selesai_at', '>=', now())
                ->latest('mulai_at')
                ->get();
        }

        $riwayat = TryOutDetail::query()
            ->with(['tryOut:id,judul', 'tryOut.kelas:id,nama'])
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        $belumPunyaKelas = ! $student->kelas_id;

        return view('siswa.tryout.index', compact('tryouts', 'riwayat', 'student', 'belumPunyaKelas'));
    }

    public function show(TryOut $tryout): View
    {
        $student = AuthenticatedStudent::profileOrFail();
        abort_unless($this->tryOutService->siswaBisaAkses($tryout, $student), 404);

        $sudahSubmit = TryOutDetail::query()
            ->where('try_out_id', $tryout->id)
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->exists();

        abort_if($sudahSubmit, 403, 'Tryout ini sudah kamu kumpulkan.');

        $soal = $this->tryOutService->soalCollection($tryout);

        return view('siswa.tryout.show', compact('tryout', 'soal'));
    }

    public function store(Request $request, TryOut $tryout): RedirectResponse
    {
        $student = AuthenticatedStudent::profileOrFail();
        abort_unless($this->tryOutService->siswaBisaAkses($tryout, $student), 404);

        $validated = $request->validate([
            'jawaban' => ['required', 'array'],
            'jawaban.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $detail = $this->tryOutService->submitAnswers($tryout, $student, $validated['jawaban']);

        ActivityLogger::log('tryout.submitted', $detail, ['try_out_id' => $tryout->id]);

        return redirect()
            ->route('siswa.tryout.index')
            ->with('success', 'Jawaban tryout berhasil dikumpulkan.');
    }
}
