<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\GroupConsultationReport;
use App\Models\Rpl;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $requests = ConsultationRequest::with('student')
            ->where(function ($query) {
                $query->whereNull('counselor_id')
                    ->orWhere('counselor_id', auth()->id());
            })
            ->latest()
            ->get();

        $individualRequests = ConsultationRequest::query()
            ->where('counselor_id', auth()->id());

        $groupReports = GroupConsultationReport::query()
            ->where('teacher_id', auth()->id());

        $individualStudentIds = $individualRequests->pluck('student_id')
            ->filter()
            ->unique();

        $groupStudentIds = Rpl::query()
            ->where('teacher_id', auth()->id())
            ->where('type', Rpl::TYPE_KELOMPOK)
            ->with('groupStudents:id')
            ->get()
            ->pluck('groupStudents.*.id')
            ->flatten()
            ->filter()
            ->unique();

        $studentCount = $individualStudentIds
            ->merge($groupStudentIds)
            ->unique()
            ->count();

        $caseCategoryIndividual = $individualRequests
            ->whereNotNull('case_category')
            ->selectRaw('case_category, count(*) as total')
            ->groupBy('case_category')
            ->pluck('total', 'case_category');

        $caseCategoryGroup = $groupReports
            ->whereNotNull('case_category')
            ->selectRaw('case_category, count(*) as total')
            ->groupBy('case_category')
            ->pluck('total', 'case_category');

        $caseCategoryStats = collect(ConsultationRequest::CASE_CATEGORIES)
            ->map(function ($label, $value) use ($caseCategoryIndividual, $caseCategoryGroup) {
                $individual = (int) ($caseCategoryIndividual[$value] ?? 0);
                $group = (int) ($caseCategoryGroup[$value] ?? 0);

                return [
                    'label' => $label,
                    'individual' => $individual,
                    'group' => $group,
                    'total' => $individual + $group,
                ];
            })
            ->values();

        $serviceTypeStats = collect([
            ['label' => 'Individu', 'value' => $individualRequests->count(), 'color' => 'bg-sky-500'],
            ['label' => 'Kelompok', 'value' => $groupReports->count(), 'color' => 'bg-emerald-500'],
        ]);

        $metrics = [
            [
                'title' => 'Jumlah siswa',
                'value' => $studentCount,
                'description' => 'Jumlah siswa yang pernah mendapat layanan Anda.',
                'color' => 'from-slate-800 to-slate-600',
            ],
            [
                'title' => 'Jumlah kasus',
                'value' => $individualRequests->count() + $groupReports->count(),
                'description' => 'Total kasus konseling individu dan kelompok.',
                'color' => 'from-indigo-600 to-cyan-500',
            ],
            [
                'title' => 'Layanan individu',
                'value' => $individualRequests->count(),
                'description' => 'Jumlah layanan konseling individu yang Anda tangani.',
                'color' => 'from-blue-600 to-sky-400',
            ],
            [
                'title' => 'Layanan kelompok',
                'value' => $groupReports->count(),
                'description' => 'Jumlah laporan konseling kelompok yang Anda buat.',
                'color' => 'from-emerald-500 to-teal-400',
            ],
        ];

        $recentStudentHistories = ConsultationRequest::query()
            ->with(['student:id,name,class_id,school_id', 'student.classModel:id,name', 'student.schoolModel:id,name'])
            ->where('counselor_id', auth()->id())
            ->where('status', ConsultationRequest::STATUS_SELESAI)
            ->latest('consultation_date')
            ->limit(8)
            ->get();

        return view('guru.dashboard', compact(
            'metrics',
            'requests',
            'caseCategoryStats',
            'serviceTypeStats',
            'recentStudentHistories'
        ));
    }
}
