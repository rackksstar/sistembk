<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SociometryResponse;
use App\Models\User;
use App\Support\SociometrySchoolScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SociometryMapController extends Controller
{
    public function index(Request $request, SociometrySchoolScope $schoolScope): View
    {
        $teacher = $request->user();
        $schoolScope->schoolIdOrFail($teacher);
        $studentIds = fn () => $schoolScope->students($teacher)->select('users.id');

        $students = $schoolScope->students($teacher)
            ->where('status', User::STATUS_APPROVED)
            ->withCount([
                'receivedSociometryChoices' => fn (Builder $query) => $query->whereIn('student_id', $studentIds()),
            ])
            ->orderByDesc('received_sociometry_choices_count')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $kelasId = $request->integer('kelas_id') ?: null;
        $filterStatus = $request->string('filled')->toString() ?: 'all';

        if ($kelasId) {
            $schoolScope->classes($teacher)->findOrFail($kelasId);
        }

        $filledUserIds = SociometryResponse::query()
            ->whereIn('student_id', $studentIds())
            ->distinct()
            ->pluck('student_id')
            ->all();

        $responsesQuery = $this->responsesForSchool($schoolScope, $teacher)->latest('submitted_at');

        if ($filterStatus === 'filled') {
            $responsesQuery->whereIn('student_id', $filledUserIds);
        }

        $classUserIds = null;
        if ($kelasId) {
            $classUserIds = $schoolScope->students($teacher)
                ->whereHas('studentProfile', fn (Builder $query) => $query->where('kelas_id', $kelasId))
                ->select('users.id');
            $responsesQuery->whereIn('student_id', $classUserIds);
        }

        $responses = $responsesQuery->get();

        $notFilledStudents = collect([]);
        if ($filterStatus === 'not_filled') {
            $notFilledQuery = $schoolScope->students($teacher)
                ->whereNotIn('id', $filledUserIds)
                ->orderBy('name');
            if ($kelasId) {
                $notFilledQuery->whereHas('studentProfile', fn (Builder $query) => $query->where('kelas_id', $kelasId));
            }
            $notFilledStudents = $notFilledQuery->get(['id', 'name']);
        }

        $popular = $students->where('received_sociometry_choices_count', '>', 0)->take(5);
        $isolated = $students->where('received_sociometry_choices_count', 0);

        $kelasList = $schoolScope->classes($teacher)
            ->with('students:id,user_id,kelas_id')
            ->get();

        $classSummaries = $kelasList->map(function ($kelas) use ($filledUserIds) {
            $userIds = $kelas->students->pluck('user_id')->filter()->unique();
            $total = $userIds->count();
            $filled = $userIds->filter(fn ($id) => in_array($id, $filledUserIds))->count();

            return [
                'id' => $kelas->id,
                'nama' => $kelas->nama,
                'total_students' => $total,
                'filled' => $filled,
                'not_filled' => $total - $filled,
            ];
        });

        return view('guru.sociometry.index', compact(
            'students',
            'responses',
            'popular',
            'isolated',
            'classSummaries',
            'kelasId',
            'filterStatus',
            'notFilledStudents'
        ));
    }

    /**
     * Preview analisis sosiometri untuk seorang siswa
     */
    public function show(int $studentId, SociometrySchoolScope $schoolScope): View
    {
        $teacher = auth()->user();
        $student = $schoolScope->students($teacher)->whereKey($studentId)->firstOrFail(['id', 'name']);
        $studentIds = fn () => $schoolScope->students($teacher)->select('users.id');

        $inbound = SociometryResponse::query()
            ->where('chosen_student_id', $student->id)
            ->whereIn('student_id', $studentIds())
            ->with('student:id,name')
            ->get();

        $outbound = SociometryResponse::query()
            ->where('student_id', $student->id)
            ->whereIn('chosen_student_id', $studentIds())
            ->with('chosenStudent:id,name')
            ->get();

        $inboundCounts = $inbound->groupBy('relation_type')->map->count();
        $totalInbound = $inbound->count();
        $totalOutbound = $outbound->count();

        // mutual choices (reciprocity)
        $inboundFrom = $inbound->pluck('student_id')->unique();
        $outboundTo = $outbound->pluck('chosen_student_id')->unique();
        $mutualIds = $inboundFrom->intersect($outboundTo);

        $mutualCount = $mutualIds->count();

        // Simple status label
        $status = $totalInbound >= 5 ? 'Populer' : ($totalInbound === 0 ? 'Terisolasi' : 'Netral');

        return view('guru.sociometry.show', compact('student', 'inbound', 'outbound', 'inboundCounts', 'totalInbound', 'totalOutbound', 'mutualCount', 'status'));
    }

    /**
     * Export rekap sosiometri per kelas (atau keseluruhan jika no kelas)
     */
    public function exportClass(Request $request, SociometrySchoolScope $schoolScope)
    {
        $teacher = $request->user();
        $kelasId = $request->integer('kelas_id') ?: null;
        $filterStatus = $request->string('filled')->toString() ?: 'all';

        $kelas = null;
        if ($kelasId) {
            $kelas = $schoolScope->classes($teacher)
                ->with('students.user')
                ->findOrFail($kelasId);
            $students = $kelas->students;
        } else {
            $students = $schoolScope->students($teacher)->with('studentProfile')->get();
        }

        $studentIds = fn () => $schoolScope->students($teacher)->select('users.id');
        $filledUserIds = SociometryResponse::query()
            ->whereIn('student_id', $studentIds())
            ->distinct()
            ->pluck('student_id')
            ->all();

        $responses = $this->responsesForSchool($schoolScope, $teacher);
        if ($kelasId) {
            $responses->whereIn('student_id', $kelas->students->pluck('user_id')->filter()->unique());
        }
        if ($filterStatus === 'filled') {
            $responses->whereIn('student_id', $filledUserIds);
        }
        if ($filterStatus === 'not_filled') {
            // handled in view by computing diff
        }

        $responses = $responses->get();

        $pdf = Pdf::loadView('guru.sociometry.pdf.class', compact('kelas', 'students', 'responses', 'filledUserIds', 'filterStatus'))
            ->setPaper('a4', 'landscape');

        $name = 'sosiometry_kelas_'.($kelas?->nama ? preg_replace('/[^A-Za-z0-9_-]/', '_', $kelas->nama) : 'all').'.pdf';

        return $pdf->download($name);
    }

    /**
     * Export analisis sosiometri per siswa ke PDF
     */
    public function exportStudent(int $studentId, SociometrySchoolScope $schoolScope)
    {
        $teacher = auth()->user();
        $student = $schoolScope->students($teacher)->whereKey($studentId)->firstOrFail(['id', 'name']);
        $studentIds = fn () => $schoolScope->students($teacher)->select('users.id');

        $inbound = SociometryResponse::query()
            ->where('chosen_student_id', $student->id)
            ->whereIn('student_id', $studentIds())
            ->with('student:id,name')
            ->get();
        $outbound = SociometryResponse::query()
            ->where('student_id', $student->id)
            ->whereIn('chosen_student_id', $studentIds())
            ->with('chosenStudent:id,name')
            ->get();

        $inboundCounts = $inbound->groupBy('relation_type')->map->count();
        $totalInbound = $inbound->count();
        $totalOutbound = $outbound->count();
        $inboundFrom = $inbound->pluck('student_id')->unique();
        $outboundTo = $outbound->pluck('chosen_student_id')->unique();
        $mutualIds = $inboundFrom->intersect($outboundTo);
        $mutualCount = $mutualIds->count();
        $status = $totalInbound >= 5 ? 'Populer' : ($totalInbound === 0 ? 'Terisolasi' : 'Netral');

        $pdf = Pdf::loadView('guru.sociometry.pdf.student', compact('student', 'inbound', 'outbound', 'inboundCounts', 'totalInbound', 'totalOutbound', 'mutualCount', 'status'));
        $name = 'sosiometry_student_'.preg_replace('/[^A-Za-z0-9_-]/', '_', $student->name).'.pdf';

        return $pdf->download($name);
    }

    /**
     * @return Builder<SociometryResponse>
     */
    private function responsesForSchool(SociometrySchoolScope $schoolScope, User $teacher): Builder
    {
        $studentIds = fn () => $schoolScope->students($teacher)->select('users.id');

        return SociometryResponse::query()
            ->whereIn('student_id', $studentIds())
            ->whereIn('chosen_student_id', $studentIds())
            ->with(['student:id,name', 'chosenStudent:id,name']);
    }
}
