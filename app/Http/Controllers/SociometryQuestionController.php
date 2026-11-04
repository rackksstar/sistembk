<?php

namespace App\Http\Controllers;

use App\Models\SociometryQuestion;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Kelola bank soal sosiometri — dipakai bersama oleh halaman admin dan Guru BK.
 * Route admin: admin.sociometry-questions.*  |  Route guru: guru.sociometry.questions.*
 */
class SociometryQuestionController extends Controller
{
    public function index(Request $request): View
    {
        $jenis = $request->string('jenis')->toString();
        $aspek = $request->string('aspek')->toString();

        $questions = SociometryQuestion::query()
            ->when(array_key_exists($jenis, SociometryQuestion::JENIS), fn ($q) => $q->where('jenis', $jenis))
            ->when(array_key_exists($aspek, SociometryQuestion::ASPEK), fn ($q) => $q->where('aspek', $aspek))
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        return view('sociometry-questions.index', [
            'questions' => $questions,
            'jenisOptions' => SociometryQuestion::JENIS,
            'aspekOptions' => SociometryQuestion::ASPEK,
            'jenis' => $jenis,
            'aspek' => $aspek,
            'routePrefix' => $request->routeIs('admin.*') ? 'admin.sociometry-questions' : 'guru.sociometry.questions',
            'backRoute' => $request->routeIs('admin.*') ? 'admin.sociometry-questions.index' : 'guru.sociometry.questions.index',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $question = SociometryQuestion::create($validated);

        ActivityLogger::log('sociometry-question.created', $question, [
            'pertanyaan' => Str::limit($question->pertanyaan, 80),
        ]);

        return back()->with('success', 'Soal sosiometri berhasil ditambahkan.');
    }

    public function update(Request $request, SociometryQuestion $sociometryQuestion): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $sociometryQuestion->update($validated);

        ActivityLogger::log('sociometry-question.updated', $sociometryQuestion, [
            'pertanyaan' => Str::limit($sociometryQuestion->pertanyaan, 80),
        ]);

        return back()->with('success', 'Soal sosiometri berhasil diperbarui.');
    }

    public function destroy(SociometryQuestion $sociometryQuestion): RedirectResponse
    {
        ActivityLogger::log('sociometry-question.deleted', $sociometryQuestion, [
            'pertanyaan' => Str::limit($sociometryQuestion->pertanyaan, 80),
        ]);

        $sociometryQuestion->delete();

        return back()->with('success', 'Soal sosiometri berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'pertanyaan' => ['required', 'string', 'max:2000'],
            'jenis' => ['required', Rule::in(array_keys(SociometryQuestion::JENIS))],
            'aspek' => ['required', Rule::in(array_keys(SociometryQuestion::ASPEK))],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
