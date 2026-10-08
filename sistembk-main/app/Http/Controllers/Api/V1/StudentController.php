<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Services\CounselorStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        private readonly CounselorStudentService $counselorStudentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->role === User::ROLE_GURU
            ? $this->counselorStudentService->queryForCounselor($user)
            : Student::query();

        $query->with([
            'user:id,name,email',
            'kelas:id,nama,sekolah_id',
            'kelas.sekolah:id,nama',
        ])->orderBy('name');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->integer('kelas_id'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', $term)
                    ->orWhere('nisn', 'like', $term);
            });
        }

        $items = $query->paginate(min(50, (int) $request->input('per_page', 15)));

        return response()->json($items);
    }
}
