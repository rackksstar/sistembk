<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreInstrumentQuestionRequest;
use App\Http\Requests\Guru\UpdateInstrumentQuestionRequest;
use App\Models\InstrumentQuestion;
use App\Models\InterestCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstrumentQuestionController extends Controller
{
    /**
     * Menampilkan daftar soal instrumen
     */
    public function index(Request $request): View
    {
        $category = $request->string('category')->toString();
        $interestCategoryId = $request->filled('interest_category_id')
            ? $request->integer('interest_category_id')
            : null;
        $jenjangTarget = $request->string('jenjang_target')->toString();

        $questions = InstrumentQuestion::query()
            ->with('interestCategory')
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($interestCategoryId, fn ($query) => $query->where('interest_category_id', $interestCategoryId))
            ->when($jenjangTarget, fn ($query) => $query->where('jenjang_target', $jenjangTarget))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $usedCategoryIds = $questions->getCollection()
            ->pluck('interest_category_id')
            ->filter()
            ->unique()
            ->values();

        $interestCategories = InterestCategory::query()
            ->where(function ($query) use ($usedCategoryIds) {
                $query->where('is_active', true);
                if ($usedCategoryIds->isNotEmpty()) {
                    $query->orWhereIn('id', $usedCategoryIds);
                }
            })
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        return view('guru.instruments.questions.index', [
            'questions' => $questions,
            'categories' => InstrumentQuestion::CATEGORIES,
            'category' => $category,
            'interestCategories' => $interestCategories,
            'interest_category_id' => $interestCategoryId,
            'jenjang_target' => $jenjangTarget,
            'jenjangTargets' => InstrumentQuestion::JENJANG_TARGETS,
        ]);
    }

    /**
     * Menyimpan soal baru ke database
     */
    public function store(StoreInstrumentQuestionRequest $request): RedirectResponse
    {
        $question = InstrumentQuestion::create($request->instrumentQuestionData() + [
            'created_by' => auth()->id(),
        ]);

        ActivityLogger::log('instrument-question.created', $question, [
            'question' => Str::limit($question->question, 80),
        ]);

        return back()->with('success', 'Soal instrumen berhasil ditambahkan.');
    }

    /**
     * Memperbarui data soal yang sudah ada
     */
    public function update(UpdateInstrumentQuestionRequest $request, InstrumentQuestion $question): RedirectResponse
    {
        $question->update($request->instrumentQuestionData());

        ActivityLogger::log('instrument-question.updated', $question, [
            'question' => Str::limit($question->question, 80),
        ]);

        return back()->with('success', 'Soal instrumen berhasil diperbarui.');
    }

    /**
     * Menghapus soal dari database
     */
    public function destroy(InstrumentQuestion $question): RedirectResponse
    {
        ActivityLogger::log('instrument-question.deleted', $question, [
            'question' => Str::limit($question->question, 80),
        ]);

        if ($question->answers()->exists()) {
            $question->delete();
        } else {
            $question->forceDelete();
        }

        return back()->with('success', 'Soal instrumen berhasil dihapus.');
    }
}
