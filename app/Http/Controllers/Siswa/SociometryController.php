<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SociometryQuestion;
use App\Models\SociometryResponse;
use App\Models\SosiometryInstrument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Modul Sosiometri (sisi siswa): siswa menjawab bank soal sosiometri aktif.
 * Tiap soal meminta memilih (positif) atau menolak (negatif) satu teman
 * beserta alasan. Mengisi ulang akan MENGGANTI seluruh jawaban sebelumnya.
 *
 * Guru bisa menonaktifkan instrumen per kelas lewat menu "Kelola Instrumen".
 * Saat kelas siswa dinonaktifkan, form disembunyikan dan penyimpanan ditolak.
 */
class SociometryController extends Controller
{
    // Form isi sosiometri + jawaban siswa yang sedang login.
    public function index(): View
    {
        $this->assertInstrumentActive();

        $students = User::query()
            ->where('role', User::ROLE_SISWA)
            ->where('id', '!=', auth()->id())
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get(['id', 'name']);

        $questions = SociometryQuestion::query()
            ->active()
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $responses = SociometryResponse::query()
            ->where('student_id', auth()->id())
            ->with('chosenStudent:id,name')
            ->get()
            ->keyBy('sociometry_question_id');

        return view('siswa.sociometry.index', compact('students', 'questions', 'responses'));
    }

    // Menyimpan jawaban sosiometri siswa (satu baris per soal aktif).
    public function store(Request $request): RedirectResponse
    {
        $this->assertInstrumentActive();

        $questions = SociometryQuestion::query()
            ->active()
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $rules = ['answers' => ['required', 'array']];
        foreach ($questions as $question) {
            $rules["answers.{$question->id}.chosen_student_id"] = ['required', 'integer', 'exists:users,id'];
            $rules["answers.{$question->id}.reason"] = ['nullable', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules, [
            'answers.*.chosen_student_id.required' => 'Setiap pertanyaan wajib dipilih.',
        ]);

        DB::transaction(function () use ($questions, $validated) {
            SociometryResponse::where('student_id', auth()->id())->delete();

            foreach ($questions as $question) {
                $chosen = $validated['answers'][$question->id]['chosen_student_id'] ?? null;

                abort_if((int) $chosen === auth()->id(), 422, 'Tidak boleh memilih diri sendiri.');

                SociometryResponse::create([
                    'student_id' => auth()->id(),
                    'chosen_student_id' => $chosen,
                    'sociometry_question_id' => $question->id,
                    'relation_type' => $question->jenis,
                    'reason' => $validated['answers'][$question->id]['reason'] ?? null,
                    'submitted_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Jawaban sosiometri berhasil disimpan.');
    }

    /**
     * Tolak aksi bila instrumen sosiometri kelas siswa dinonaktifkan guru.
     * Kelas tanpa baris pengaturan dianggap aktif (default).
     */
    private function assertInstrumentActive(): void
    {
        $kelasId = auth()->user()?->studentProfile?->kelas_id;

        if (! $kelasId) {
            return;
        }

        $instrument = SosiometryInstrument::query()->where('kelas_id', $kelasId)->first();

        if ($instrument && ! $instrument->is_active) {
            abort(403, 'Instrumen sosiometri untuk kelas Anda sedang dinonaktifkan. Silakan hubungi guru BK.');
        }
    }
}
