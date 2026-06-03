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

        return view('guru.tryout.create', compact('kelas', 'soal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'durasi_menit' => ['required', 'integer', 'min:5', 'max:180'],
            'mulai_at' => ['required', 'date'],
            'selesai_at' => ['required', 'date', 'after:mulai_at'],
            'status' => ['required', Rule::in(array_keys(TryOut::STATUSES))],
            'kelas_ids' => ['required', 'array', 'min:1'],
            'kelas_ids.*' => ['integer', 'exists:kelas,id'],
            'soal_ids' => ['required', 'array', 'min:1'],
            'soal_ids.*' => ['integer', 'exists:master_questions,id'],
        ]);

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
        abort_unless($tryout->counselor_id === auth()->id(), 403);

        $tryout->load([
            'kelas',
            'details.student.user',
        ]);

        $rataKeseluruhan = round($tryout->details->avg('rata_skor') ?? 0, 1);

        return view('guru.tryout.show', compact('tryout', 'rataKeseluruhan'));
    }
}
