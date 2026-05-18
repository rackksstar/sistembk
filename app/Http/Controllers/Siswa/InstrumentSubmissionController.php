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

            $result = $this->scoreResult($validated['category'], $totalScore, $questions->count());

            $submission = InstrumentSubmission::create([
                'student_id' => auth()->id(),
                'category' => $validated['category'],
                'total_score' => $totalScore,
                'result_label' => $result['label'],
                'result_description' => $result['description'],
                'submitted_at' => now(),
            ]);

            $submission->answers()->saveMany($answerRows);
        });

        return redirect()
            ->route('siswa.instruments.index', ['category' => $validated['category']])
            ->with('success', 'Jawaban instrumen berhasil dikirim dan diskor otomatis.');
    }

    private function scoreResult(string $category, int $score, int $questionCount): array
    {
        $maxScore = max($questionCount * 4, 1);
        $percentage = ($score / $maxScore) * 100;

        if ($category === InstrumentQuestion::CATEGORY_ANGKET_MASALAH) {
            return match (true) {
                $percentage >= 70 => ['label' => 'Prioritas Tinggi', 'description' => 'Siswa membutuhkan perhatian dan tindak lanjut Guru BK lebih cepat.'],
                $percentage >= 40 => ['label' => 'Perlu Dipantau', 'description' => 'Ada beberapa area masalah yang perlu didalami melalui percakapan lanjutan.'],
                default => ['label' => 'Ringan', 'description' => 'Belum tampak indikasi masalah berat dari jawaban instrumen.'],
            };
        }

        return match (true) {
            $percentage >= 70 => ['label' => 'Sangat Menonjol', 'description' => 'Potensi atau kecenderungan siswa terlihat kuat pada instrumen ini.'],
            $percentage >= 40 => ['label' => 'Cukup Berkembang', 'description' => 'Potensi siswa sudah terlihat dan dapat diperkuat melalui bimbingan.'],
            default => ['label' => 'Perlu Eksplorasi', 'description' => 'Siswa masih perlu mengeksplorasi diri pada area ini.'],
        };
    }
}
