<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MasterQuestion;
use App\Models\TryOut;
use App\Services\TryOutService;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TryoutController extends Controller
{
    public function __construct(
        private readonly TryOutService $tryOutService
    ) {}

    public function index(): View
    {
        $tryouts = TryOut::query()
            ->where('counselor_id', auth()->id())
            ->with(['kelas:id,nama'])
            ->withCount('details')
            ->latest()
            ->paginate(10);

        return view('guru.tryout.index', compact('tryouts'));
    }

    public function create(): View
    {
        [$kelas, $soal] = $this->formOptions();

        return view('guru.tryout.create', compact('kelas', 'soal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTryout($request);

        $tryout = $this->tryOutService->createForCounselor(
            auth()->id(),
            $validated,
            $validated['kelas_ids'],
            $validated['soal_ids']
        );

        ActivityLogger::log('tryout.created', $tryout, ['judul' => $validated['judul']]);

        return redirect()
            ->route('guru.tryout.index')
            ->with('success', 'Tryout berhasil dibuat.');
    }

    public function show(TryOut $tryout): View
    {
        $this->authorizeTryout($tryout);

        $tryout->load([
            'kelas',
            'details.student.user',
        ]);

        $rataKeseluruhan = round($tryout->details->avg('rata_skor') ?? 0, 1);

        return view('guru.tryout.show', compact('tryout', 'rataKeseluruhan'));
    }

    public function edit(TryOut $tryout): View
    {
        $this->authorizeTryout($tryout);

        $tryout->load('kelas');
        [$kelas, $soal] = $this->formOptions();
        $locked = $tryout->hasSubmissions();

        return view('guru.tryout.edit', compact('tryout', 'kelas', 'soal', 'locked'));
    }

    public function update(Request $request, TryOut $tryout): RedirectResponse
    {
        $this->authorizeTryout($tryout);

        $validated = $this->validateTryout($request, $tryout);

        $this->tryOutService->updateForCounselor(
            $tryout,
            $validated,
            $validated['kelas_ids'] ?? [],
            $validated['soal_ids'] ?? []
        );

        ActivityLogger::log('tryout.updated', $tryout, ['judul' => $validated['judul']]);

        return redirect()
            ->route('guru.tryout.index')
            ->with('success', 'Tryout berhasil diperbarui.');
    }

    public function destroy(TryOut $tryout): RedirectResponse
    {
        $this->authorizeTryout($tryout);

        try {
            ActivityLogger::log('tryout.deleted', $tryout, ['judul' => $tryout->judul]);
            $this->tryOutService->deleteForCounselor($tryout);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'tryout' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('guru.tryout.index')
            ->with('success', 'Tryout berhasil dihapus.');
    }

    private function authorizeTryout(TryOut $tryout): void
    {
        abort_unless($tryout->counselor_id === auth()->id(), 403);
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    private function formOptions(): array
    {
        $kelasQuery = Kelas::query()->orderBy('nama');

        $user = auth()->user()->loadMissing('guruBkProfile');

        if ($sekolahId = $user->guruBkProfile?->sekolah_id) {
            $kelasQuery->where('sekolah_id', $sekolahId);
        }

        $kelas = $kelasQuery->get();
        $soal = MasterQuestion::query()
            ->where('kategori', MasterQuestion::KATEGORI_TRYOUT)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        return [$kelas, $soal];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTryout(Request $request, ?TryOut $tryout = null): array
    {
        $locked = $tryout?->hasSubmissions() ?? false;

        $rules = [
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'durasi_menit' => ['required', 'integer', 'min:5', 'max:180'],
            'mulai_at' => ['required', 'date'],
            'selesai_at' => ['required', 'date', 'after:mulai_at'],
            'status' => ['required', Rule::in(array_keys(TryOut::STATUSES))],
        ];

        if (! $locked) {
            $rules['kelas_ids'] = ['required', 'array', 'min:1'];
            $rules['kelas_ids.*'] = ['integer', 'exists:kelas,id'];
            $rules['soal_ids'] = ['required', 'array', 'min:1'];
            $rules['soal_ids.*'] = ['integer', 'exists:master_questions,id'];
        }

        $validated = $request->validate($rules);

        if ($locked) {
            $validated['kelas_ids'] = $tryout->kelas->pluck('id')->all();
            $validated['soal_ids'] = $tryout->soal_ids ?? [];
        }

        return $validated;
    }
}
