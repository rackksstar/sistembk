<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AssessmentResponse;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(): View
    {
        $classIds = auth()->user()
            ->studentProfile()
            ->with('guidanceClasses:id')
            ->first()
            ?->guidanceClasses
            ->pluck('id')
            ->all() ?? [];

        $classmates = Student::query()
            ->whereHas('guidanceClasses', fn ($query) => $query->whereIn('guidance_classes.id', $classIds))
            ->where('user_id', '!=', auth()->id())
            ->with('user:id,name')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        if ($classmates->isEmpty()) {
            $classmates = collect([
                (object) ['id' => 1001, 'name' => 'Alya Putri'],
                (object) ['id' => 1002, 'name' => 'Bima Pratama'],
                (object) ['id' => 1003, 'name' => 'Citra Lestari'],
                (object) ['id' => 1004, 'name' => 'Dimas Arya'],
                (object) ['id' => 1005, 'name' => 'Nadia Kirana'],
            ]);
        }

        $latestAssessment = AssessmentResponse::where('student_id', auth()->id())->latest()->first();

        return view('siswa.assessments.index', compact('classmates', 'latestAssessment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'interest_area' => ['required', 'string', 'max:120'],
            'favorite_subject' => ['required', 'string', 'max:120'],
            'strengths' => ['required', 'string', 'max:1000'],
            'career_goal' => ['nullable', 'string', 'max:160'],
            'closest_friend_id' => ['required', 'integer'],
            'study_friend_id' => ['required', 'integer', 'different:closest_friend_id'],
            'social_reason' => ['required', 'string', 'max:1000'],
        ], [
            'study_friend_id.different' => 'Pilih teman belajar yang berbeda dari teman terdekat.',
        ]);

        AssessmentResponse::create([
            'student_id' => auth()->id(),
            'talent_interest' => [
                'interest_area' => $validated['interest_area'],
                'favorite_subject' => $validated['favorite_subject'],
                'strengths' => $validated['strengths'],
                'career_goal' => $validated['career_goal'] ?? null,
            ],
            'sociometry' => [
                'closest_friend_id' => $validated['closest_friend_id'],
                'study_friend_id' => $validated['study_friend_id'],
                'social_reason' => $validated['social_reason'],
            ],
            'status' => AssessmentResponse::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('siswa.assessments.index')
            ->with('success', 'Asesmen berhasil dikirim. Terima kasih sudah mengisi dengan jujur.');
    }
}
