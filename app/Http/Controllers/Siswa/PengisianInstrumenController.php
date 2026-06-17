<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\HasilInstrumen;
use App\Models\JawabanInstrumen;
use App\Models\PertanyaanInstrumen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengisianInstrumenController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category', PertanyaanInstrumen::KATEGORI_MINAT_BAKAT)->toString();
        abort_unless(array_key_exists($category, PertanyaanInstrumen::CATEGORIES), 404);

        $questions = PertanyaanInstrumen::query()
            ->where('category', $category)
            ->where('is_active', true)
            ->oldest()
            ->get();

        $latestSubmissions = HasilInstrumen::query()
            ->where('student_id', auth()->id())
            ->latest('submitted_at')
            ->get()
            ->unique('category')
            ->keyBy('category');

        return view('siswa.instruments.index', [
            'categories' => PertanyaanInstrumen::CATEGORIES,
            'category' => $category,
            'questions' => $questions,
            'latestSubmissions' => $latestSubmissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(PertanyaanInstrumen::CATEGORIES))],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['required', 'integer', 'min:0'],
        ]);

        $questions = PertanyaanInstrumen::query()
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
                $answerRows[] = new JawabanInstrumen([
                    'instrument_question_id' => $question->id,
                    'answer_label' => $option['label'],
                    'score' => $score,
                ]);
            }

            $maxScore = $questions->count() * 5;
            $percentage = $this->calculatePercentage($totalScore, $maxScore);
            $result = $this->scoreResult($validated['category'], $percentage);

            $submission = HasilInstrumen::create([
                'student_id' => auth()->id(),
                'category' => $validated['category'],
                'total_score' => $totalScore,
                'percentage' => $percentage,
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

    private function calculatePercentage(int $score, int $maxScore): float
    {
        return round($maxScore > 0 ? ($score / $maxScore) * 100 : 0, 2);
    }

    private function scoreResult(string $category, float $percentage): array
    {
        if ($category === PertanyaanInstrumen::KATEGORI_ANGKET_MASALAH) {
            return match (true) {
                $percentage >= 80 => ['label' => 'Prioritas Tinggi', 'description' => 'Siswa membutuhkan perhatian dan tindak lanjut Guru BK lebih cepat.'],
                $percentage >= 60 => ['label' => 'Perlu Dipantau', 'description' => 'Ada beberapa area masalah yang perlu didalami melalui percakapan lanjutan.'],
                default => ['label' => 'Ringan', 'description' => 'Belum tampak indikasi masalah berat dari jawaban instrumen.'],
            };
        }

        return match (true) {
            $percentage >= 80 => ['label' => 'Tinggi / Siap', 'description' => 'Siswa menunjukkan kecenderungan kuat dalam area ini dan siap untuk pengembangan lebih lanjut.'],
            $percentage >= 60 => ['label' => 'Cukup', 'description' => 'Siswa berada pada level yang stabil namun masih perlu pendalaman lebih lanjut.'],
            default => ['label' => 'Rendah', 'description' => 'Siswa memerlukan bimbingan tambahan untuk mengembangkan area ini.'],
        };
    }
}
