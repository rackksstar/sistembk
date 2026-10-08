<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\InstrumentAnswer;
use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstrumentSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)->toString();
        abort_unless(array_key_exists($category, InstrumentQuestion::CATEGORIES), 404);

        $questions = InstrumentQuestion::query()
            ->where('category', $category)
            ->where('is_active', true)
            ->oldest()
            ->get();

        $latestSubmissions = InstrumentSubmission::query()
            ->where('student_id', auth()->id())
            ->latest('submitted_at')
            ->get()
            ->unique('category')
            ->keyBy('category');

        return view('siswa.instruments.index', [
            'categories' => InstrumentQuestion::CATEGORIES,
            'category' => $category,
            'questions' => $questions,
            'latestSubmissions' => $latestSubmissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(InstrumentQuestion::CATEGORIES))],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['required', 'integer', 'min:0'],
        ]);

        $questions = InstrumentQuestion::query()
            ->where('category', $validated['category'])
            ->where('is_active', true)
            ->whereIn('id', array_keys($validated['answers']))
            ->get()
            ->keyBy('id');

        if ($questions->count() !== count($validated['answers'])) {
            return back()->withErrors(['answers' => 'Jawaban tidak sesuai dengan daftar soal aktif.'])->withInput();
        }

        DB::transaction(function () use ($validated, $questions) {
            $totalScore = 0;
            $answerRows = [];

            foreach ($validated['answers'] as $questionId => $optionIndex) {
                $question = $questions[(int) $questionId];
                $option = $question->options[$optionIndex] ?? null;

                if (! $option) {
                    abort(422, 'Pilihan jawaban tidak valid.');
                }

                $score = (int) $option['score'];
                $totalScore += $score;
                $answerRows[] = new InstrumentAnswer([
                    'instrument_question_id' => $question->id,
                    'answer_label' => $option['label'],
                    'score' => $score,
                ]);
            }

            $result = $this->scoreResult($totalScore, $questions->count());

            $submission = InstrumentSubmission::create([
                'student_id' => auth()->id(),
                'category' => $validated['category'],
                'total_score' => $totalScore,
                'result_label' => $result['label'],
                'result_description' => $result['description'],
                'submitted_at' => now(),
            ]);

            $submission->answers()->saveMany($answerRows);

            if ($validated['category'] === InstrumentQuestion::CATEGORY_MINAT_BAKAT) {
                $submission->load('answers.question:id,talent_code');
                $dominantCode = $submission->dominantRiasecCode();
                $submission->update([
                    'result_label' => $dominantCode,
                    'result_description' => "Kode dominan Talents Mapping (RIASEC): {$dominantCode}. Lihat rincian lengkap di halaman hasil.",
                ]);
            }
        });

        if ($validated['category'] === InstrumentQuestion::CATEGORY_STRATEGI_BELAJAR) {
            return redirect()
                ->route('siswa.instruments.strategi-belajar-result')
                ->with('success', 'Jawaban instrumen berhasil dikirim dan diskor otomatis.');
        }

        if ($validated['category'] === InstrumentQuestion::CATEGORY_MINAT_BAKAT) {
            return redirect()
                ->route('siswa.instruments.minat-bakat-result')
                ->with('success', 'Jawaban instrumen berhasil dikirim dan diskor otomatis.');
        }

        return redirect()
            ->route('siswa.instruments.index', ['category' => $validated['category']])
            ->with('success', 'Jawaban instrumen berhasil dikirim dan diskor otomatis.');
    }

    /**
     * Rincian hasil Strategi Belajar per bagian (Perencanaan/Eksekusi/Refleksi),
     * mengikuti format kartu hasil ruangguru: status + strategi yang bisa dicoba.
     */
    public function strategiBelajarResult(): View
    {
        $submission = InstrumentSubmission::query()
            ->where('student_id', auth()->id())
            ->where('category', InstrumentQuestion::CATEGORY_STRATEGI_BELAJAR)
            ->with('answers.question:id,section')
            ->latest('submitted_at')
            ->first();

        abort_unless($submission, 404);

        return view('siswa.instruments.strategi-belajar-result', [
            'submission' => $submission,
            'results' => $submission->strategiBelajarSectionResults(),
        ]);
    }

    /**
     * Hasil Minat Bakat berbasis kerangka Talents Mapping (RIASEC): skor per
     * dimensi dan kode dominan.
     */
    public function minatBakatResult(): View
    {
        $submission = InstrumentSubmission::query()
            ->where('student_id', auth()->id())
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->with('answers.question:id,talent_code')
            ->latest('submitted_at')
            ->first();

        abort_unless($submission, 404);

        $results = $submission->riasecScores();

        return view('siswa.instruments.minat-bakat-result', [
            'submission' => $submission,
            'results' => $results,
            'dominantCode' => collect($results)->take(3)->pluck('code')->implode(''),
        ]);
    }

    private function scoreResult(int $score, int $questionCount): array
    {
        $maxScore = max($questionCount * 5, 1);
        $percentage = ($score / $maxScore) * 100;

        return match (true) {
            $percentage >= 70 => ['label' => 'Sangat Menonjol', 'description' => 'Potensi atau kecenderungan siswa terlihat kuat pada instrumen ini.'],
            $percentage >= 40 => ['label' => 'Cukup Berkembang', 'description' => 'Potensi siswa sudah terlihat dan dapat diperkuat melalui bimbingan.'],
            default => ['label' => 'Perlu Eksplorasi', 'description' => 'Siswa masih perlu mengeksplorasi diri pada area ini.'],
        };
    }
}
