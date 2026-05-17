<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\SociometryResponse;
use App\Models\User;
use Illuminate\View\View;

class SociometryMapController extends Controller
{
    public function index(): View
    {
        $students = User::query()
            ->where('role', User::ROLE_SISWA)
            ->where('status', User::STATUS_APPROVED)
            ->withCount('receivedSociometryChoices')
            ->orderByDesc('received_sociometry_choices_count')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $responses = SociometryResponse::query()
            ->with(['student:id,name', 'chosenStudent:id,name'])
            ->latest('submitted_at')
            ->get();

        $popular = $students->where('received_sociometry_choices_count', '>', 0)->take(5);
        $isolated = $students->where('received_sociometry_choices_count', 0);

        return view('guru.sociometry.index', compact('students', 'responses', 'popular', 'isolated'));
    }
}
