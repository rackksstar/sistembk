<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\InstrumentQuestion;
use App\Models\InstrumentSubmission;
use App\Services\CounselorStudentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstrumentResultController extends Controller
{
    public function __construct(
        private readonly CounselorStudentService $counselorStudentService,
    ) {}

    public function index(Request $request): View
    {
        $module = $request->string('module', 'yola')->toString();
        if (! in_array($module, ['yola', 'key'], true)) {
            $module = 'yola';
        }

        $categories = $module === 'key'
            ? InstrumentQuestion::KEY_CATEGORIES
            : InstrumentQuestion::YOLA_CATEGORIES;

        $category = $request->string('category')->toString();
        if ($category !== '' && ! array_key_exists($category, $categories)) {
            $category = '';
        }

        if ($module === 'key' && $category === '') {
            $category = InstrumentQuestion::CATEGORY_MINAT_BAKAT;
        }

        $accessibleUserIds = $this->counselorStudentService
            ->queryForCounselor($request->user())
            ->pluck('user_id');

        $submissions = InstrumentSubmission::query()
            ->with([
                'student:id,name,email,school,school_id,class_id',
                'student.schoolModel:id,name',
                'student.classModel:id,name',
                'answers.question:id,question,section,talent_code',
            ])
            ->whereIn('student_id', $accessibleUserIds)
            ->whereIn('category', array_keys($categories))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->latest('submitted_at')
            ->paginate(12)
            ->withQueryString();

        return view('guru.instruments.results.index', [
            'module' => $module,
            'submissions' => $submissions,
            'categories' => $categories,
            'category' => $category,
        ]);
    }

    /**
     * Matriks Talents Mapping: baris siswa × kolom dimensi RIASEC,
     * dari submission Minat Bakat terakhir tiap siswa (scoped ke siswa BK).
     */
    public function talentMatrix(Request $request): View
    {
        $accessibleUserIds = $this->counselorStudentService
            ->queryForCounselor($request->user())
            ->pluck('user_id');

        $submissions = InstrumentSubmission::query()
            ->whereIn('category', [
                InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                InstrumentQuestion::CATEGORY_MINAT_KERJA,
            ])
            ->whereNotNull('kode_minat')
            ->where('kode_minat', '!=', '')
            ->whereIn('student_id', $accessibleUserIds)
            ->with([
                'student:id,name,email,school,school_id,class_id',
                'student.schoolModel:id,name',
                'student.classModel:id,name',
                'answers.question:id,talent_code,interest_category_id',
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
