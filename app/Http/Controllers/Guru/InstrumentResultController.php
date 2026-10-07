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
        $category = $request->string('category')->toString();

        $accessibleUserIds = $this->counselorStudentService
            ->queryForCounselor($request->user())
            ->pluck('user_id');

        $submissions = InstrumentSubmission::query()
            ->with([
                'student:id,name,email,school,school_id,class_id',
                'student.schoolModel:id,name',
                'student.classModel:id,name',
                'answers.question:id,question',
            ])
            ->whereIn('student_id', $accessibleUserIds)
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
}
