<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\GroupConsultationReport;
use App\Models\Rpl;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceStatisticController extends Controller
{
    public function index(Request $request): View
    {
        $year = $request->integer('year') ?: now()->year;

        $individual = ConsultationRequest::query()
            ->where('counselor_id', auth()->id())
            ->where('status', ConsultationRequest::STATUS_SELESAI)
            ->whereYear('consultation_date', $year)
            ->get(['student_id', 'case_category', 'consultation_date']);

        $group = GroupConsultationReport::query()
            ->where('teacher_id', auth()->id())
            ->whereYear('service_date', $year)
            ->get(['case_category', 'service_date']);

        $individualStudentIds = $individual->pluck('student_id')
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

        $summary = [
            'studentCount' => $individualStudentIds->merge($groupStudentIds)->unique()->count(),
            'individual' => $individual->count(),
            'group' => $group->count(),
            'total' => $individual->count() + $group->count(),
            'dominantCategory' => $this->dominantCategory($categoryStats = collect(ConsultationRequest::CASE_CATEGORIES)
                ->map(function ($label, $value) use ($individual, $group) {
                    $individualCount = $individual->where('case_category', $value)->count();
                    $groupCount = $group->where('case_category', $value)->count();

                    return [
                        'value' => $value,
                        'label' => $label,
                        'individual' => $individualCount,
                        'group' => $groupCount,
                        'total' => $individualCount + $groupCount,
                    ];
                })
                ->values()),
        ];

        $monthlyStats = collect(range(1, 12))->map(function (int $month) use ($individual, $group) {
            $individualTotal = $individual->filter(fn ($item) => (int) optional($item->consultation_date)->format('n') === $month)->count();
            $groupTotal = $group->filter(fn ($item) => (int) optional($item->service_date)->format('n') === $month)->count();

            return [
                'month' => $month,
                'label' => date('M', mktime(0, 0, 0, $month, 1)),
                'individual' => $individualTotal,
                'group' => $groupTotal,
                'total' => $individualTotal + $groupTotal,
            ];
        });

        $categoryStats = collect(ConsultationRequest::CASE_CATEGORIES)
            ->map(function ($label, $value) use ($individual, $group) {
                $individualCount = $individual->where('case_category', $value)->count();
                $groupCount = $group->where('case_category', $value)->count();

                return [
                    'label' => $label,
                    'individual' => $individualCount,
                    'group' => $groupCount,
                    'total' => $individualCount + $groupCount,
                ];
            })
            ->values();

        $serviceTypeStats = collect([
            ['label' => 'Individu', 'value' => $individual->count(), 'color' => 'rgb(56, 189, 248)'],
            ['label' => 'Kelompok', 'value' => $group->count(), 'color' => 'rgb(16, 185, 129)'],
        ]);

        return view('guru.service-statistics.index', compact('year', 'categoryStats', 'monthlyStats', 'summary', 'serviceTypeStats'));
    }

    private function dominantCategory(Collection $categoryStats): string
    {
        $dominant = $categoryStats->sortByDesc('total')->first();

        return $dominant && $dominant['total'] > 0 ? $dominant['label'] : '-';
    }
}
