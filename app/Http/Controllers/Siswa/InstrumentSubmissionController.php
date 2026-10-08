<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\InstrumentAnswer;
use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use App\Models\InterestCategory;
use App\Services\Minat\InterestResult;
use App\Services\Minat\InterestScoringService;
use App\Services\Minat\RecommendationService;
use App\Support\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InstrumentSubmissionController extends Controller
{
    public function __construct(
        private readonly InterestScoringService $interestScoringService,
        private readonly RecommendationService $recommendationService,
    ) {}

    /**
     * Index modul Yola: Minat Bakat Kerja + asesmen diri klasik.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $rawCategory = $request->string('category')->toString();

        // Alias lama / label UI Yola.
        if ($rawCategory === 'strategi_belajar') {
            return redirect()->route('siswa.instruments.index', ['category' => InstrumentQuestion::CATEGORY_GAYA_BELAJAR]);
        }

        // Link lama minat_bakat (sebelum dipisah) → Minat Bakat Kuliah (Key).
        if ($rawCategory === InstrumentQuestion::CATEGORY_MINAT_BAKAT) {
            return redirect()->route('siswa.minat-bakat.index');
        }

        $category = $rawCategory !== ''
            ? $rawCategory
            : InstrumentQuestion::CATEGORY_MINAT_KERJA;
        abort_unless(InstrumentQuestion::isYolaCategory($category), 404);

        $latestSubmissions = InstrumentSubmission::query()
            ->where('student_id', auth()->id())
            ->whereIn('category', array_keys(InstrumentQuestion::YOLA_CATEGORIES))
            ->latest('submitted_at')
            ->get()
            ->unique('category')
            ->keyBy('category');

        // Minat Bakat Kerja memakai bank soal RIASEC (Talents Mapping) yang sama
        // dengan jalur kuliah, tetapi hasilnya diarahkan ke rekomendasi bidang karier.
        if ($category === InstrumentQuestion::CATEGORY_MINAT_KERJA) {
            return $this->minatKerjaIndex($request, $latestSubmissions);
        }

        $questions = InstrumentQuestion::query()
            ->where('category', $category)
            ->where('is_active', true)
            ->oldest()
            ->get();

        return view('siswa.instruments.index', [
            'module' => 'yola',
            'categories' => InstrumentQuestion::YOLA_CATEGORIES,
            'category' => $category,
            'questions' => $questions,
            'latestSubmissions' => $latestSubmissions,
            'useRiasecWizard' => false,
            'useTalentsLikert' => false,
            'jenjang' => null,
            'needsJenjangChooser' => false,
            'jenjangBlocked' => false,
            'oldAnswers' => collect(old('answers', [])),
        ]);
    }

    /**
     * Index Minat Bakat Kerja — selaras sistembk-main:
     * 99 item Talents Mapping, form Likert satu halaman, hasil Holland + karier.
     */
    private function minatKerjaIndex(Request $request, Collection $latestSubmissions): View
    {
        $profileJenjang = $this->resolveProfileJenjang();
        $jenjang = in_array((string) $profileJenjang, ['SMA', 'SMK'], true)
            ? $profileJenjang
            : null;

        // Ref tidak memfilter jenjang; bank soal RIASEC (jenjang_target=semua).
        $questions = InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->active()
            ->where(function ($query) {
                $query->whereNotNull('talent_code')
                    ->orWhereNotNull('interest_category_id');
            })
            ->with('interestCategory')
            ->oldest()
            ->get();

        return view('siswa.instruments.index', [
            'module' => 'yola',
            'categories' => InstrumentQuestion::YOLA_CATEGORIES,
            'category' => InstrumentQuestion::CATEGORY_MINAT_KERJA,
            'questions' => $questions,
            'latestSubmissions' => $latestSubmissions,
            'useRiasecWizard' => false,
            'useTalentsLikert' => true,
            'jenjang' => $jenjang,
            'needsJenjangChooser' => false,
            'jenjangBlocked' => in_array($profileJenjang, ['SD', 'SMP'], true),
            'oldAnswers' => collect(old('answers', [])),
        ]);
    }

    /**
     * Index modul Key: Minat Bakat Kuliah (RIASEC) + rekomendasi prodi PCR / bidang karier.
     */
    public function minatBakatIndex(Request $request): View
    {
        $category = InstrumentQuestion::CATEGORY_MINAT_BAKAT;
        $profileJenjang = $this->resolveProfileJenjang();
        $jenjang = $profileJenjang;
        $needsJenjangChooser = false;

        if ($jenjang && ! in_array($jenjang, ['SMA', 'SMK', 'SD', 'SMP'], true)) {
            $jenjang = null;
        }

        if (! $jenjang) {
            $requestedJenjang = strtoupper((string) $request->query('jenjang', ''));
            if (in_array($requestedJenjang, ['SMA', 'SMK'], true)) {
                $jenjang = $requestedJenjang;
            }
        }

        $needsJenjangChooser = ! in_array((string) $jenjang, ['SMA', 'SMK'], true)
            && ! in_array((string) $profileJenjang, ['SD', 'SMP'], true);

        $questionsQuery = InstrumentQuestion::query()
            ->where('category', $category)
            ->active()
            ->whereNotNull('interest_category_id')
            ->whereHas('interestCategory', fn ($q) => $q->active())
            ->with('interestCategory')
            ->oldest();

        if (in_array((string) $jenjang, ['SMA', 'SMK'], true)) {
            $questionsQuery->forJenjang($jenjang);
        } else {
            $questionsQuery->whereRaw('0 = 1');
        }

        $questions = $questionsQuery->get();

        $latestSubmission = InstrumentSubmission::query()
            ->where('student_id', auth()->id())
            ->where('category', $category)
            ->latest('submitted_at')
            ->first();

        return view('siswa.minat-bakat.index', [
            'module' => 'key',
            'category' => $category,
            'questions' => $questions,
            'latestSubmission' => $latestSubmission,
            'oldAnswers' => collect(old('answers', [])),
            'profileJenjang' => $profileJenjang,
            'jenjang' => $jenjang,
            'needsJenjangChooser' => $needsJenjangChooser,
            'jenjangBlocked' => in_array($profileJenjang, ['SD', 'SMP'], true),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(InstrumentQuestion::CATEGORIES))],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['required', 'integer', 'min:0'],
            'jenjang' => ['nullable', Rule::in(['SMA', 'SMK'])],
        ]);

        if ($validated['category'] === InstrumentQuestion::CATEGORY_MINAT_BAKAT) {
            return $this->storeMinatBakat($request, $validated);
        }

        if ($validated['category'] === InstrumentQuestion::CATEGORY_MINAT_KERJA) {
            return $this->storeMinatKerja($request, $validated);
        }

        $questions = InstrumentQuestion::query()
            ->where('category', $validated['category'])
            ->where('is_active', true)
            ->oldest()
            ->get()
            ->keyBy('id');

        if ($questions->isEmpty()) {
            return back()->withErrors(['answers' => 'Soal instrumen belum tersedia untuk kategori ini.'])->withInput();
        }

        $answerIds = collect(array_keys($validated['answers']))->map(fn ($id) => (int) $id)->sort()->values();
        $questionIds = $questions->keys()->map(fn ($id) => (int) $id)->sort()->values();
        if ($answerIds->all() !== $questionIds->all()) {
            return back()->withErrors(['answers' => 'Jawaban tidak sesuai dengan daftar soal aktif.'])->withInput();
        }

        $submission = DB::transaction(function () use ($validated, $questions) {
            $totalScore = 0;
            $answerRows = [];

            foreach ($validated['answers'] as $questionId => $optionIndex) {
                $question = $questions[(int) $questionId];
                $options = array_values($question->options ?? []);
                $option = $options[(int) $optionIndex] ?? null;

                if (! $option) {
                    abort(422, 'Pilihan jawaban tidak valid.');
                }

                $score = (int) $option['score'];
                $totalScore += $score;
                $answerRows[] = new InstrumentAnswer([
                    'instrument_question_id' => $question->id,
                    'answer_label' => $option['label'] ?? '',
                    'score' => $score,
                ]);
            }

            $maxPerQuestion = $questions->max(function (InstrumentQuestion $question) {
                $scores = collect($question->options ?? [])
                    ->filter(fn ($option) => is_array($option) && array_key_exists('score', $option))
                    ->map(fn ($option) => (int) $option['score']);

                return $scores->max() ?: 0;
            }) ?: 4;

            $result = $this->scoreResult($validated['category'], $totalScore, $questions->count(), (int) $maxPerQuestion);

            $submission = InstrumentSubmission::create([
                'student_id' => auth()->id(),
                'category' => $validated['category'],
                'total_score' => $totalScore,
                'percentage' => $result['percentage'],
                'result_label' => $result['label'],
                'result_description' => $result['description'],
                'submitted_at' => now(),
            ]);

            $submission->answers()->saveMany($answerRows);

            return $submission;
        });

        // Strategi Belajar (ref): ke halaman hasil per-bagian.
        if ($validated['category'] === InstrumentQuestion::CATEGORY_GAYA_BELAJAR) {
            return redirect()
                ->route('siswa.instruments.strategi-belajar-result')
                ->with('success', 'Jawaban instrumen berhasil dikirim dan diskor otomatis.');
        }

        // Instrumen Yola lain: langsung ke halaman hasil (bukan kembali ke form).
        if (InstrumentQuestion::isYolaCategory($validated['category'])) {
            return redirect()
                ->route('siswa.instruments.hasil', $submission)
                ->with('success', 'Jawaban instrumen berhasil dikirim dan diskor otomatis.');
        }

        return redirect()
            ->route('siswa.instruments.index', ['category' => $validated['category']])
            ->with('success', 'Jawaban instrumen berhasil dikirim dan diskor otomatis.');
    }

    /**
     * Hasil Strategi Belajar per bagian (Perencanaan / Eksekusi / Refleksi).
     */
    public function strategiBelajarResult(): View
    {
        $submission = InstrumentSubmission::query()
            ->where('student_id', auth()->id())
            ->where('category', InstrumentQuestion::CATEGORY_GAYA_BELAJAR)
            ->with('answers.question:id,section')
            ->latest('submitted_at')
            ->first();

        abort_unless($submission, 404);

        $sectionResults = $submission->strategiBelajarSectionResults();

        if ($sectionResults === []) {
            return view('siswa.instruments.hasil-simple', [
                'submission' => $submission,
            ]);
        }

        return view('siswa.instruments.strategi-belajar-result', [
            'submission' => $submission,
            'results' => $sectionResults,
        ]);
    }

    public function hasil(InstrumentSubmission $submission): View
    {
        $this->assertOwnsSubmission($submission);

        if (in_array($submission->category, [
            InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            InstrumentQuestion::CATEGORY_MINAT_KERJA,
        ], true) && filled($submission->kode_minat)) {
            [$result, $recommendations] = $this->buildMinatResultPayload($submission);
            $track = $submission->category === InstrumentQuestion::CATEGORY_MINAT_KERJA
                ? 'kerja'
                : 'kuliah';

            return view('siswa.instruments.hasil', [
                'submission' => $submission,
                'result' => $result,
                'topCategories' => $result->topCategories(3),
                'recommendations' => $recommendations,
                'track' => $track,
            ]);
        }

        if ($submission->category === InstrumentQuestion::CATEGORY_GAYA_BELAJAR) {
            $sectionResults = $submission->strategiBelajarSectionResults();
            if ($sectionResults !== []) {
                return view('siswa.instruments.strategi-belajar-result', [
                    'submission' => $submission,
                    'results' => $sectionResults,
                ]);
            }
        }

        return view('siswa.instruments.hasil-simple', [
            'submission' => $submission,
        ]);
    }

    public function hasilPdf(InstrumentSubmission $submission): Response
    {
        $this->assertOwnsSubmission($submission);
        abort_unless($submission->category === InstrumentQuestion::CATEGORY_MINAT_BAKAT, 404);

        [$result, $recommendations] = $this->buildMinatResultPayload($submission);

        $user = auth()->user();
        $student = $user?->studentProfile?->loadMissing('kelas.sekolah');
        $tanggalCetak = now()->format('d M Y');

        ActivityLogger::log('instrument.minat.pdf.downloaded', $submission, [
            'submission_id' => $submission->id,
            'kode_minat' => $submission->kode_minat,
            'jenjang' => $submission->jenjang,
        ]);

        $pdf = Pdf::loadView('siswa.instruments.pdf', compact(
            'submission',
            'result',
            'recommendations',
            'user',
            'student',
            'tanggalCetak',
        ))->setPaper('a4', 'portrait');

        $namaFile = 'hasil-minat-'.str($user?->name ?? 'siswa')->slug().'-'.now()->format('Ymd').'.pdf';

        return $pdf->download($namaFile);
    }

    /**
     * @param  array{category: string, answers: array<int|string, int>, jenjang?: string|null}  $validated
     */
    private function storeMinatBakat(Request $request, array $validated): RedirectResponse
    {
        return $this->storeRiasecAssessment(
            $request,
            $validated,
            InstrumentQuestion::CATEGORY_MINAT_BAKAT,
            'Asesmen minat bakat kuliah berhasil dikirim.',
        );
    }

    /**
     * @param  array{category: string, answers: array<int|string, int>, jenjang?: string|null}  $validated
     */
    private function storeMinatKerja(Request $request, array $validated): RedirectResponse
    {
        return $this->storeRiasecAssessment(
            $request,
            $validated,
            InstrumentQuestion::CATEGORY_MINAT_KERJA,
            'Asesmen minat bakat kerja berhasil dikirim.',
        );
    }

    /**
     * Simpan asesmen Talents Mapping (RIASEC) untuk jalur kerja atau kuliah.
     * Soal selalu diambil dari bank kategori minat_bakat.
     *
     * @param  array{category: string, answers: array<int|string, int>, jenjang?: string|null}  $validated
     */
    private function storeRiasecAssessment(
        Request $request,
        array $validated,
        string $submissionCategory,
        string $successMessage,
    ): RedirectResponse {
        $isKerja = $submissionCategory === InstrumentQuestion::CATEGORY_MINAT_KERJA;
        $jenjang = $this->resolveProfileJenjang();

        // Jalur kuliah (Key): jenjang wajib untuk rekomendasi PCR vs karier.
        // Jalur kerja (Yola/ref): jenjang opsional — soal RIASEC tidak tergantung jenjang.
        if (! $isKerja) {
            if (! $jenjang) {
                $request->validate([
                    'jenjang' => ['required', Rule::in(['SMA', 'SMK'])],
                ], [
                    'jenjang.required' => 'Pilih jenjang SMA atau SMK sebelum mengirim asesmen.',
                ]);
                $jenjang = strtoupper((string) $validated['jenjang']);
            }

            if (in_array($jenjang, ['SD', 'SMP'], true)) {
                return back()->withErrors([
                    'jenjang' => 'Asesmen ini untuk siswa SMA/SMK.',
                ])->withInput();
            }

            if (! in_array($jenjang, ['SMA', 'SMK'], true)) {
                return back()->withErrors([
                    'jenjang' => 'Jenjang tidak valid. Pilih SMA atau SMK.',
                ])->withInput();
            }
        } else {
            if ($jenjang && ! in_array($jenjang, ['SMA', 'SMK'], true)) {
                $jenjang = in_array(strtoupper((string) ($validated['jenjang'] ?? '')), ['SMA', 'SMK'], true)
                    ? strtoupper((string) $validated['jenjang'])
                    : null;
            } elseif (! $jenjang && in_array(strtoupper((string) ($validated['jenjang'] ?? '')), ['SMA', 'SMK'], true)) {
                $jenjang = strtoupper((string) $validated['jenjang']);
            }

            if (in_array((string) $this->resolveProfileJenjang(), ['SD', 'SMP'], true)) {
                return back()->withErrors([
                    'jenjang' => 'Asesmen ini untuk siswa SMA/SMK.',
                ])->withInput();
            }
        }

        $questionsQuery = InstrumentQuestion::query()
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->active()
            ->where(function ($query) {
                $query->whereNotNull('talent_code')
                    ->orWhereNotNull('interest_category_id');
            })
            ->with('interestCategory');

        if (! $isKerja && in_array((string) $jenjang, ['SMA', 'SMK'], true)) {
            $questionsQuery->forJenjang($jenjang);
        }

        $questions = $questionsQuery->get()->keyBy('id');

        if ($questions->isEmpty()) {
            return back()->withErrors([
                'answers' => 'Soal asesmen minat bakat belum tersedia.',
            ])->withInput();
        }

        if ($questions->count() !== count($validated['answers'])) {
            return back()->withErrors(['answers' => 'Jawaban tidak sesuai dengan daftar soal aktif.'])->withInput();
        }

        $answerIds = collect(array_keys($validated['answers']))->map(fn ($id) => (int) $id)->sort()->values();
        $questionIds = $questions->keys()->map(fn ($id) => (int) $id)->sort()->values();
        if ($answerIds->all() !== $questionIds->all()) {
            return back()->withErrors(['answers' => 'Jawaban tidak sesuai dengan daftar soal aktif.'])->withInput();
        }

        $interestResult = $this->interestScoringService->score($questions, $validated['answers']);
        $trackLabel = $submissionCategory === InstrumentQuestion::CATEGORY_MINAT_KERJA
            ? 'jalur kerja'
            : 'lanjut kuliah';

        $submission = DB::transaction(function () use ($questions, $validated, $interestResult, $jenjang, $submissionCategory, $trackLabel) {
            $answerRows = [];

            foreach ($validated['answers'] as $questionId => $optionIndex) {
                $question = $questions[(int) $questionId];
                $options = array_values($question->options ?? []);
                $option = $options[(int) $optionIndex] ?? null;

                if (! $option) {
                    abort(422, 'Pilihan jawaban tidak valid.');
                }

                $answerRows[] = new InstrumentAnswer([
                    'instrument_question_id' => $question->id,
                    'answer_label' => $option['label'] ?? '',
                    'score' => (int) $option['score'],
                ]);
            }

            $submission = InstrumentSubmission::create([
                'student_id' => auth()->id(),
                'category' => $submissionCategory,
                'jenjang' => $jenjang,
                'kode_minat' => $interestResult->kode_minat,
                'category_scores' => $interestResult->toArray(),
                'dominant_interest_id' => $interestResult->dominant_interest_id,
                'secondary_interest_id' => $interestResult->secondary_interest_id,
                'is_tied' => $interestResult->is_tied,
                'total_score' => (int) round((float) $interestResult->total_score),
                'result_label' => $interestResult->kode_minat,
                'result_description' => "Kode Minat Talents Mapping ({$trackLabel}): {$interestResult->kode_minat}. Lihat rincian lengkap di halaman hasil.",
                'submitted_at' => now(),
            ]);

            $submission->answers()->saveMany($answerRows);

            return $submission;
        });

        ActivityLogger::log('instrument.minat.submitted', $submission, [
            'submission_id' => $submission->id,
            'category' => $submissionCategory,
            'kode_minat' => $submission->kode_minat,
            'jenjang' => $submission->jenjang,
        ]);

        return redirect()
            ->route('siswa.instruments.hasil', $submission)
            ->with('success', $successMessage);
    }

    /**
     * @return array{0: InterestResult, 1: array{items: list<array<string, mixed>>, empty_message: ?string}}
     */
    private function buildMinatResultPayload(InstrumentSubmission $submission): array
    {
        $scores = $submission->category_scores ?? [];
        $result = InterestResult::fromSnapshot($scores, [
            'kode_minat' => $submission->kode_minat,
            'dominant_interest_id' => $submission->dominant_interest_id,
            'secondary_interest_id' => $submission->secondary_interest_id,
            'is_tied' => (bool) $submission->is_tied,
            'result_label' => $submission->result_label,
            'result_description' => $submission->result_description,
            'total_score' => $submission->total_score,
        ]);

        $result = $this->enrichResultDescriptions($result);

        if ($result->isInconclusive()) {
            return [$result, [
                'items' => [],
                'empty_message' => 'Hasil skor belum cukup untuk rekomendasi. Ulangi asesmen dengan jawaban yang lebih akurat.',
            ]];
        }

        // Jalur kerja selalu rekomendasi bidang karier; jalur kuliah mengikuti jenjang.
        if ($submission->category === InstrumentQuestion::CATEGORY_MINAT_KERJA) {
            return [$result, $this->recommendationService->forSmk($result)];
        }

        $jenjang = strtoupper((string) $submission->jenjang);
        if ($jenjang === 'SMK') {
            $recommendations = $this->recommendationService->forSmk($result);
        } elseif ($jenjang === 'SMA') {
            $recommendations = $this->recommendationService->forSma($result);
        } else {
            $recommendations = [
                'items' => [],
                'empty_message' => 'Jenjang asesmen tidak diketahui, sehingga rekomendasi tidak dapat ditampilkan.',
            ];
        }

        return [$result, $recommendations];
    }

    private function enrichResultDescriptions(InterestResult $result): InterestResult
    {
        $ids = collect($result->rankedCategories)->pluck('id')->filter()->all();
        if ($ids === []) {
            return $result;
        }

        $descriptions = InterestCategory::query()
            ->whereIn('id', $ids)
            ->pluck('deskripsi', 'id');

        $ranked = array_map(static function (array $category) use ($descriptions): array {
            if (($category['deskripsi'] ?? null) === null) {
                $category['deskripsi'] = $descriptions[$category['id']] ?? null;
            }

            return $category;
        }, $result->rankedCategories);

        return new InterestResult(
            rankedCategories: $ranked,
            kode_minat: $result->kode_minat,
            dominant_interest_id: $result->dominant_interest_id,
            secondary_interest_id: $result->secondary_interest_id,
            is_tied: $result->is_tied,
            result_label: $result->result_label,
            result_description: $result->result_description ?? ($ranked[0]['deskripsi'] ?? null),
            total_score: $result->total_score,
        );
    }

    private function resolveProfileJenjang(): ?string
    {
        $jenjang = auth()->user()?->studentProfile?->kelas?->jenjang;
        if (! is_string($jenjang) || trim($jenjang) === '') {
            return null;
        }

        return strtoupper(trim($jenjang));
    }

    private function assertOwnsSubmission(InstrumentSubmission $submission): void
    {
        abort_unless((int) $submission->student_id === (int) auth()->id(), 403);
    }

    /**
     * @param  Collection<int, InstrumentQuestion>  $questions
     * @return Collection<int, array{title: ?string, questions: Collection}>
     */
    private function groupClassicSections($questions, string $category)
    {
        if ($questions->isEmpty()) {
            return collect();
        }

        if ($category === InstrumentQuestion::CATEGORY_GAYA_BELAJAR && $questions->count() >= 3) {
            $titles = ['Perencanaan Belajar', 'Pelaksanaan Belajar', 'Evaluasi Belajar'];
            $chunks = $questions->chunk((int) ceil($questions->count() / 3))->values();

            return $chunks->map(fn ($items, $index) => [
                'title' => $titles[$index] ?? ('Bagian '.($index + 1)),
                'questions' => $items->values(),
            ]);
        }

        return collect([
            [
                'title' => null,
                'questions' => $questions->values(),
            ],
        ]);
    }

    private function scoreResult(string $category, int $score, int $questionCount, int $maxPerQuestion = 4): array
    {
        $maxScore = max($questionCount * max($maxPerQuestion, 1), 1);
        $percentage = round(($score / $maxScore) * 100, 2);

        $result = $category === InstrumentQuestion::CATEGORY_ANGKET_MASALAH
            ? match (true) {
                $percentage >= 70 => ['label' => 'Prioritas Tinggi', 'description' => 'Siswa membutuhkan perhatian dan tindak lanjut Guru BK lebih cepat.'],
                $percentage >= 40 => ['label' => 'Perlu Dipantau', 'description' => 'Ada beberapa area masalah yang perlu didalami melalui percakapan lanjutan.'],
                default => ['label' => 'Ringan', 'description' => 'Belum tampak indikasi masalah berat dari jawaban instrumen.'],
            }
        : match (true) {
            $percentage >= 70 => ['label' => 'Sangat Menonjol', 'description' => 'Potensi atau kecenderungan siswa terlihat kuat pada instrumen ini.'],
            $percentage >= 40 => ['label' => 'Cukup Berkembang', 'description' => 'Potensi siswa sudah terlihat dan dapat diperkuat melalui bimbingan.'],
            default => ['label' => 'Perlu Eksplorasi', 'description' => 'Siswa masih perlu mengeksplorasi diri pada area ini.'],
        };

        return $result + ['percentage' => $percentage];
    }
}
