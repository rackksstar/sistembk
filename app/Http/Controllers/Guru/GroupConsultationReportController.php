<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\GroupConsultationReport;
use App\Models\Rpl;
use App\Models\School;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupConsultationReportController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $category = $request->string('case_category')->toString();
        $rplId = $request->integer('rpl_id') ?: null;
        $schoolId = $request->integer('school_id') ?: null;

        $reports = GroupConsultationReport::query()
            ->with(['rpl.groupStudents:id,name', 'classRoom:id,name,school_id', 'classRoom.school:id,name'])
            ->where('teacher_id', auth()->id())
            ->when($search, fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('result', 'like', "%{$search}%")
                    ->orWhere('evaluation', 'like', "%{$search}%")
                    ->orWhere('follow_up', 'like', "%{$search}%")
                    ->orWhereHas('rpl', fn ($rpl) => $rpl->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('classRoom', fn ($class) => $class->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('rpl.groupStudents', fn ($student) => $student->where('name', 'like', "%{$search}%"));
            }))
            ->when($status, fn ($query) => $query->whereHas('rpl', fn ($rpl) => $rpl->where('status', $status)))
            ->when($category, fn ($query) => $query->where('case_category', $category))
            ->when($rplId, fn ($query) => $query->where('rpl_id', $rplId))
            ->when($schoolId, fn ($query) => $query->whereHas('classRoom', fn ($class) => $class->where('school_id', $schoolId)))
            ->latest('service_date')
            ->paginate(10)
            ->withQueryString();

        $groupRpls = Rpl::query()
            ->where('teacher_id', auth()->id())
            ->where('type', Rpl::TYPE_KELOMPOK)
            ->with(['classRoom:id,name', 'groupStudents:id,name'])
            ->latest()
            ->get();

        return view('guru.group-reports.index', [
            'reports' => $reports,
            'groupRpls' => $groupRpls,
            'search' => $search,
            'status' => $status,
            'statuses' => Rpl::STATUSES,
            'caseCategories' => ConsultationRequest::CASE_CATEGORIES,
            'category' => $category,
            'rplId' => $rplId,
            'schoolId' => $schoolId,
            'schoolOptions' => School::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $rpl = $this->teacherGroupRpl((int) $data['rpl_id']);

        GroupConsultationReport::create($data + [
            'teacher_id' => auth()->id(),
            'class_id' => $rpl->class_id,
        ]);

        $rpl->update(['status' => Rpl::STATUS_SELESAI]);

        return back()->with('success', 'Laporan konseling kelompok berhasil dibuat.');
    }

    public function update(Request $request, GroupConsultationReport $groupReport): RedirectResponse
    {
        abort_unless($groupReport->teacher_id === auth()->id(), 403);

        $data = $this->validatedData($request);
        $rpl = $this->teacherGroupRpl((int) $data['rpl_id']);

        $groupReport->update($data + ['class_id' => $rpl->class_id]);

        return back()->with('success', 'Laporan konseling kelompok berhasil diperbarui.');
    }

    public function destroy(GroupConsultationReport $groupReport): RedirectResponse
    {
        abort_unless($groupReport->teacher_id === auth()->id(), 403);

        $groupReport->delete();

        return back()->with('success', 'Laporan konseling kelompok berhasil dihapus.');
    }

    public function print(GroupConsultationReport $groupReport)
    {
        abort_unless($groupReport->teacher_id === auth()->id(), 403);

        $groupReport->load(['teacher.schoolModel', 'rpl.groupStudents', 'classRoom']);

        return Pdf::loadView('guru.group-reports.print', compact('groupReport'))
            ->setPaper('a4')
            ->stream('laporan-konseling-kelompok-'.$groupReport->id.'.pdf');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'rpl_id' => ['required', 'integer', 'exists:rpls,id'],
            'title' => ['required', 'string', 'max:255'],
            'service_date' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'location' => ['nullable', 'string', 'max:255'],
            'case_category' => ['required', Rule::in(array_keys(ConsultationRequest::CASE_CATEGORIES))],
            'result' => ['required', 'string', 'max:4000'],
            'evaluation' => ['required', 'string', 'max:4000'],
            'follow_up' => ['nullable', 'string', 'max:4000'],
        ]);
    }

    private function teacherGroupRpl(int $rplId): Rpl
    {
        return Rpl::query()
            ->where('teacher_id', auth()->id())
            ->where('type', Rpl::TYPE_KELOMPOK)
            ->findOrFail($rplId);
    }
}
