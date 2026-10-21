<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Student;
use App\Services\CounselorStudentService;
use App\Services\StudentImporter;
use App\Support\ActivityLogger;
use App\Support\StudentSpreadsheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function __construct(
        private readonly CounselorStudentService $counselorStudentService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $students = $this->counselorStudentService
            ->queryForCounselor(auth()->user())
            ->with(['kelas:id,nama,sekolah_id', 'kelas.sekolah:id,nama'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhere('school', 'like', "%{$search}%")
                        ->orWhereHas('kelas', fn ($k) => $k->where('nama', 'like', "%{$search}%"))
                        ->orWhereHas('kelas.sekolah', fn ($s) => $s->where('nama', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $kelasList = $this->kelasForCounselor();

        return view('guru.students.index', compact('students', 'search', 'kelasList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $student = Student::create($this->validatedData($request));

        ActivityLogger::log('siswa.created', $student, [
            'nama' => $student->name,
            'nisn' => $student->nisn,
        ]);

        return back()->with('success', 'Data NISN dan tanggal lahir siswa berhasil disimpan.');
    }

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
            $this->kelasForCounselor()->pluck('id')->all(),
        );

        $skipped = count($hasil['errors']);

        ActivityLogger::log('siswa.imported', null, [
            'file' => $request->file('import_file')->getClientOriginalName(),
            'baru' => $hasil['created'],
            'diperbarui' => $hasil['updated'],
            'dilewati' => $skipped,
        ]);

        return back()
            ->with('success', "Import selesai. {$hasil['created']} data baru, {$hasil['updated']} data diperbarui, {$skipped} baris dilewati.")
            ->with('import_errors', $hasil['errors']);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        abort_unless($this->counselorStudentService->canAccess($student, auth()->user()), 403);

        $student->update($this->validatedData($request, $student));

        ActivityLogger::log('siswa.updated', $student, [
            'nama' => $student->name,
            'nisn' => $student->nisn,
        ]);

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        abort_unless($this->counselorStudentService->canAccess($student, auth()->user()), 403);

        ActivityLogger::log('siswa.deleted', $student, [
            'nama' => $student->name,
            'nisn' => $student->nisn,
        ]);

        $student->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Student $student = null): array
    {
        $allowedKelasIds = $this->kelasForCounselor()->pluck('id')->all();

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nisn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'nisn')->ignore($student),
            ],
            'birth_date' => ['required', 'date', 'before:today'],
            'school' => ['nullable', 'string', 'max:255'],
            'kelas_id' => ['required', 'integer', Rule::in($allowedKelasIds)],
        ], [
            'nisn.unique' => 'NISN sudah digunakan siswa lain.',
            'birth_date.before' => 'Tanggal lahir harus valid dan sebelum hari ini.',
            'kelas_id.required' => 'Pilih kelas BK agar siswa masuk cakupan sekolah Anda.',
            'kelas_id.in' => 'Kelas tidak valid untuk sekolah Anda.',
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Kelas>
     */
    private function kelasForCounselor()
    {
        $user = auth()->user()->loadMissing('guruBkProfile');
        $query = Kelas::query()->with('sekolah:id,nama')->orderBy('nama');

        if ($sekolahId = $user->guruBkProfile?->sekolah_id) {
            $query->where('sekolah_id', $sekolahId);
        }

        return $query->get(['id', 'nama', 'sekolah_id']);
    }

    /**
     * Unduh template Excel berisi judul kolom beserta dropdown kelas yang
     * memang tersedia bagi guru tersebut (disaring per sekolah).
     */
    public function template(): StreamedResponse
    {
        return StudentSpreadsheet::download(
            StudentSpreadsheet::label($this->kelasForCounselor()),
            'template-import-siswa.xlsx'
        );
    }
}
