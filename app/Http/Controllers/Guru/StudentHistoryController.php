<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\GroupConsultationReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $studentId = $request->integer('student_id') ?: null;

        $students = User::query()
            ->where('role', User::ROLE_SISWA)
            ->where('status', User::STATUS_APPROVED)
            ->with(['classModel:id,name', 'schoolModel:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'class_id', 'school_id', 'school']);

        $selectedStudent = $studentId
            ? $students->firstWhere('id', $studentId)
            : $students->first();

        $individualHistories = collect();
        $groupHistories = collect();

        if ($selectedStudent) {
            $individualHistories = ConsultationRequest::query()
                ->with(['rpl:id,title', 'counselor:id,name'])
                ->where('student_id', $selectedStudent->id)
                ->where('counselor_id', auth()->id())
                ->where('status', ConsultationRequest::STATUS_SELESAI)
                ->latest('consultation_date')
                ->get();

            $groupHistories = GroupConsultationReport::query()
                ->with(['rpl.groupStudents:id,name', 'classRoom:id,name'])
                ->where('teacher_id', auth()->id())
                ->whereHas('rpl.groupStudents', fn ($query) => $query->where('users.id', $selectedStudent->id))
                ->latest('service_date')
                ->get();
        }

        return view('guru.student-histories.index', compact(
            'students',
            'selectedStudent',
            'individualHistories',
            'groupHistories'
        ));
    }
}
