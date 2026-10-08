<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstrumentResultController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category')->toString();

        $submissions = InstrumentSubmission::query()
            ->with(['student:id,name,email,school,school_id,class_id', 'student.schoolModel:id,name', 'student.classModel:id,name', 'answers.question:id,question,section,talent_code'])
            ->when($category, fn ($query) => $query->where('category', $category))
            ->latest('submitted_at')
            ->paginate(12)
            ->withQueryString();

        return view('guru.instruments.results.index', [
            'submissions' => $submissions,
            'categories' => InstrumentQuestion::CATEGORIES,
            'category' => $category,
        ]);
    }

    /**
     * Matriks Talents Mapping: baris siswa x kolom dimensi RIASEC, dari
     * submission Minat Bakat terakhir tiap siswa.
     */
    public function talentMatrix(): View
    {
        $submissions = InstrumentSubmission::query()
            ->where('category', InstrumentQuestion::CATEGORY_MINAT_BAKAT)
            ->with([
                'student:id,name,email,school,school_id,class_id',
                'student.schoolModel:id,name',
                'student.classModel:id,name',
                'answers.question:id,talent_code',
            ])
            ->latest('submitted_at')
            ->get()
            ->unique('student_id')
            ->values();

        return view('guru.instruments.results.matrix', [
            'submissions' => $submissions,
            'riasecCodes' => InstrumentQuestion::RIASEC_CODES,
        ]);
    }
}
