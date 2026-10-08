<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGuidanceClassRequest;
use App\Models\GuidanceClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuidanceClassController extends Controller
{
    public function index(): View
    {
        $classes = GuidanceClass::with('students')->latest()->paginate(8);
        $students = Student::orderBy('name')->get();

        return view('admin.guidance-classes.index', compact('classes', 'students'));
    }

    public function store(StoreGuidanceClassRequest $request): RedirectResponse
    {
        GuidanceClass::create([
            ...$request->validated(),
            'code' => 'BK-'.Str::upper(Str::random(6)),
        ]);

        return back()->with('success', 'Kelas bimbingan berhasil dibuat.');
    }

    public function update(StoreGuidanceClassRequest $request, GuidanceClass $guidanceClass): RedirectResponse
    {
        $guidanceClass->update($request->validated());

        return back()->with('success', 'Kelas bimbingan berhasil diperbarui.');
    }

    public function destroy(GuidanceClass $guidanceClass): RedirectResponse
    {
        $guidanceClass->delete();

        return back()->with('success', 'Kelas bimbingan berhasil dihapus.');
    }

    public function attachStudent(Request $request, GuidanceClass $guidanceClass): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $guidanceClass->students()->syncWithoutDetaching([$data['student_id']]);

        return back()->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    public function detachStudent(GuidanceClass $guidanceClass, Student $student): RedirectResponse
    {
        $guidanceClass->students()->detach($student->id);

        return back()->with('success', 'Siswa berhasil dihapus dari kelas.');
    }
}
