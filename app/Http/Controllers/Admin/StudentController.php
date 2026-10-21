<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\Kelas;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentImporter;
use App\Support\ActivityLogger;
use App\Support\StudentSpreadsheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $kelasId = $request->integer('kelas_id') ?: null;

        $students = Student::with(['user', 'kelas.sekolah'])
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

        $linkedUserIds = Student::query()
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->values();

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
     * Unduh template Excel berisi judul kolom beserta dropdown seluruh kelas.
     *
     * Admin mencakup semua sekolah sehingga daftarnya tidak disaring.
     */
    public function template(): StreamedResponse
    {
        return StudentSpreadsheet::download(
            StudentSpreadsheet::label(Kelas::query()->orderBy('nama')->get()),
            'template-import-siswa.xlsx'
        );
    }

    /**
     * Impor data siswa dari CSV/XLSX. Baris tanpa kelas valid ditolak per
     * baris dengan pesan, bukan disimpan dengan kelas kosong.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => ['required', 'file', 'max:2048', 'extensions:csv,txt,xlsx'],
        ], [
            'import_file.required' => 'Pilih file terlebih dahulu.',
            'import_file.extensions' => 'File harus berformat .csv atau .xlsx.',
        ]);

        $hasil = app(StudentImporter::class)->run(
            $request->file('import_file')->getRealPath(),
            Kelas::query()->pluck('id')->all(),
        );

        $skipped = count($hasil['errors']);

        ActivityLogger::log('siswa.imported', null, [
            'file' => $request->file('import_file')->getClientOriginalName(),
            'baru' => $hasil['created'],
            'diperbarui' => $hasil['updated'],
            'dilewati' => $skipped,
            'oleh' => 'admin',
        ]);

        return back()
            ->with('success', "Import selesai. {$hasil['created']} data baru, {$hasil['updated']} data diperbarui, {$skipped} baris dilewati.")
            ->with('import_errors', $hasil['errors']);
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
