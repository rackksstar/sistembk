<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\Kelas;
use App\Models\Student;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $kelasId = $request->integer('kelas_id') ?: null;

        $students = Student::with(['user', 'kelas'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhere('school', 'like', "%{$search}%");
                });
            })
            ->when($kelasId, fn ($query) => $query->where('kelas_id', $kelasId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $linkedUserIds = $students->pluck('user_id')->filter()->values();

        $studentUsers = User::query()
            ->where('role', User::ROLE_SISWA)
            ->where(fn ($query) => $query
                ->whereDoesntHave('studentProfile')
                ->orWhereIn('id', $linkedUserIds))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        $kelasList = Kelas::with('sekolah')->orderBy('nama')->get();

        return view('admin.students.index', compact('students', 'studentUsers', 'search', 'kelasId', 'kelasList'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $student = Student::create($this->payload($request->validated()));

        ActivityLogger::log('student.created', $student);

        return back()->with('success', 'Data siswa berhasil dibuat.');
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($this->payload($request->validated()));

        ActivityLogger::log('student.updated', $student);

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function payload(array $validated): array
    {
        $validated['status_biodata'] = (! empty($validated['jenis_kelamin']) && ! empty($validated['alamat']))
            ? 'lengkap'
            : 'belum_lengkap';

        return $validated;
    }

    public function destroy(Student $student): RedirectResponse
    {
        ActivityLogger::log('student.deleted', $student, ['name' => $student->name]);

        $student->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}
