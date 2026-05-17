<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SociometryResponse;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SociometryController extends Controller
{
    public function index(): View
    {
        $students = User::query()
            ->where('role', User::ROLE_SISWA)
            ->where('id', '!=', auth()->id())
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get(['id', 'name']);

        $responses = SociometryResponse::query()
            ->where('student_id', auth()->id())
            ->with('chosenStudent:id,name')
            ->latest()
            ->get();

        return view('siswa.sociometry.index', compact('students', 'responses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'close_friend_id' => ['required', 'integer', 'exists:users,id', 'different:study_friend_id'],
            'study_friend_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [
            'close_friend_id.different' => 'Teman dekat dan teman belajar harus berbeda.',
        ]);

        foreach ([$validated['close_friend_id'], $validated['study_friend_id']] as $studentId) {
            abort_if((int) $studentId === auth()->id(), 422, 'Tidak boleh memilih diri sendiri.');
        }

        DB::transaction(function () use ($validated) {
            SociometryResponse::where('student_id', auth()->id())->delete();

            SociometryResponse::create([
                'student_id' => auth()->id(),
                'chosen_student_id' => $validated['close_friend_id'],
                'relation_type' => SociometryResponse::TYPE_CLOSE_FRIEND,
                'reason' => $validated['reason'] ?? null,
                'submitted_at' => now(),
            ]);

            SociometryResponse::create([
                'student_id' => auth()->id(),
                'chosen_student_id' => $validated['study_friend_id'],
                'relation_type' => SociometryResponse::TYPE_STUDY_FRIEND,
                'reason' => $validated['reason'] ?? null,
                'submitted_at' => now(),
            ]);
        });

        return back()->with('success', 'Pilihan sosiometri berhasil disimpan.');
    }
}
