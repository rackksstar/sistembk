<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SociometryResponse;
use App\Models\User;
use App\Models\Kelas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SociometryMapController extends Controller
{
    public function index(Request $request): View
    {
        // Basic student popularity list (existing)
        $students = User::query()
            ->where('role', User::ROLE_SISWA)
            ->where('status', User::STATUS_APPROVED)
            ->withCount('receivedSociometryChoices')
            ->orderByDesc('received_sociometry_choices_count')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // All responses (optionally filter by kelas)
        $kelasId = $request->integer('kelas_id') ?: null;
        $filterStatus = $request->string('filled')->toString() ?: 'all';

        // precompute filled student user ids
        $filledUserIds = SociometryResponse::query()->distinct()->pluck('student_id')->toArray();

        $responsesQuery = SociometryResponse::query()
            ->with(['student:id,name', 'chosenStudent:id,name'])
            ->latest('submitted_at');

        if ($kelasId) {
            // Map kelas -> user ids
            $userIds = Kelas::query()
                ->where('id', $kelasId)
                ->with('students:user_id')
                ->get()
                ->flatMap(fn ($k) => $k->students->pluck('user_id'))
                ->unique()
                ->toArray();

            $responsesQuery->whereIn('student_id', $userIds);
        }

        if ($filterStatus === 'filled') {
            $responsesQuery->whereIn('student_id', $filledUserIds);
        }

        $responses = $responsesQuery->get();

        $notFilledStudents = collect([]);
        if ($filterStatus === 'not_filled') {
            // determine users in scope (kelas or all) who haven't filled
            $scopeUserIds = [];
            if ($kelasId) {
                $scopeUserIds = Kelas::query()
                    ->where('id', $kelasId)
                    ->with('students:user_id')
                    ->get()
                    ->flatMap(fn ($k) => $k->students->pluck('user_id'))
                    ->unique()
                    ->toArray();
            } else {
                $scopeUserIds = User::query()->where('role', User::ROLE_SISWA)->pluck('id')->toArray();
            }

            $notFilledIds = array_values(array_diff($scopeUserIds, $filledUserIds));
            $notFilledStudents = User::query()->whereIn('id', $notFilledIds)->get(['id', 'name']);
        }

        // Popular / isolated as before
        $popular = $students->where('received_sociometry_choices_count', '>', 0)->take(5);
        $isolated = $students->where('received_sociometry_choices_count', 0);

        // Class summaries: total, filled, not_filled
        $kelasList = Kelas::with('students:user_id,kelas_id')->get();

        // precompute filled student user ids
        $filledUserIds = SociometryResponse::query()->distinct()->pluck('student_id')->toArray();

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

        return view('guru.sociometry.index', compact('students', 'responses', 'popular', 'isolated', 'classSummaries', 'kelasId'));
    }

    /**
     * Preview analisis sosiometri untuk seorang siswa
     */
    public function show(int $studentId): View
    {
        $student = User::query()->where('id', $studentId)->where('role', User::ROLE_SISWA)->firstOrFail(['id', 'name']);

        $inbound = SociometryResponse::query()
            ->where('chosen_student_id', $student->id)
            ->with('student:id,name')
            ->get();

        $outbound = SociometryResponse::query()
            ->where('student_id', $student->id)
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
    public function exportClass(Request $request)
    {
        $kelasId = $request->integer('kelas_id') ?: null;
        $filterStatus = $request->string('filled')->toString() ?: 'all';

        $kelas = null;
        $students = collect([]);

        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            $students = $kelas ? $kelas->students()->with('user')->get() : collect([]);
        } else {
            $students = User::query()->where('role', User::ROLE_SISWA)->get();
        }

        $filledUserIds = SociometryResponse::query()->distinct()->pluck('student_id')->toArray();

        $responses = SociometryResponse::query()->with(['student:id,name', 'chosenStudent:id,name']);
        if ($kelasId && $kelas) {
            $userIds = $kelas->students->pluck('user_id')->toArray();
            $responses->whereIn('student_id', $userIds);
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

        $name = 'sosiometry_kelas_' . ($kelas?->nama ? preg_replace('/[^A-Za-z0-9_-]/', '_', $kelas->nama) : 'all') . '.pdf';
        return $pdf->download($name);
    }

    /**
     * Export analisis sosiometri per siswa ke PDF
     */
    public function exportStudent(int $studentId)
    {
        $student = User::query()->where('id', $studentId)->where('role', User::ROLE_SISWA)->firstOrFail(['id', 'name']);

        $inbound = SociometryResponse::query()->where('chosen_student_id', $student->id)->with('student:id,name')->get();
        $outbound = SociometryResponse::query()->where('student_id', $student->id)->with('chosenStudent:id,name')->get();

        $inboundCounts = $inbound->groupBy('relation_type')->map->count();
        $totalInbound = $inbound->count();
        $totalOutbound = $outbound->count();
        $inboundFrom = $inbound->pluck('student_id')->unique();
        $outboundTo = $outbound->pluck('chosen_student_id')->unique();
        $mutualIds = $inboundFrom->intersect($outboundTo);
        $mutualCount = $mutualIds->count();
        $status = $totalInbound >= 5 ? 'Populer' : ($totalInbound === 0 ? 'Terisolasi' : 'Netral');

        $pdf = Pdf::loadView('guru.sociometry.pdf.student', compact('student', 'inbound', 'outbound', 'inboundCounts', 'totalInbound', 'totalOutbound', 'mutualCount', 'status'));
        $name = 'sosiometry_student_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $student->name) . '.pdf';
        return $pdf->download($name);
    }
}
