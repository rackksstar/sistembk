<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $students = Student::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('school', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('guru.students.index', compact('students', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        Student::create($this->validatedData($request));

        return back()->with('success', 'Data NISN dan tanggal lahir siswa berhasil disimpan.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'csv_file.required' => 'Pilih file CSV terlebih dahulu.',
            'csv_file.mimes' => 'File harus berformat CSV.',
        ]);

        $file = fopen($request->file('csv_file')->getRealPath(), 'r');
        $headers = fgetcsv($file);

        if (! $headers) {
            throw ValidationException::withMessages([
                'csv_file' => 'File CSV kosong atau tidak dapat dibaca.',
            ]);
        }

        $headers = array_map(fn ($header) => Str::of($header)->lower()->trim()->replace(' ', '_')->toString(), $headers);
        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($headers, array_slice(array_pad($row, count($headers), null), 0, count($headers)));

            if (! $data) {
                $skipped++;
                continue;
            }

            $name = trim($data['nama'] ?? $data['name'] ?? '');
            $nisn = trim($data['nisn'] ?? '');
            $birthDate = $this->parseCsvDate($data['tanggal_lahir'] ?? $data['birth_date'] ?? null);
            $school = trim($data['sekolah'] ?? $data['school'] ?? '') ?: null;

            if ($name === '' || $nisn === '' || ! $birthDate) {
                $skipped++;
                continue;
            }

            $student = Student::updateOrCreate(
                ['nisn' => $nisn],
                [
                    'name' => $name,
                    'birth_date' => $birthDate,
                    'school' => $school,
                ]
            );

            $student->wasRecentlyCreated ? $created++ : $updated++;
        }

        fclose($file);

        return back()->with('success', "Import selesai. {$created} data baru, {$updated} data diperbarui, {$skipped} baris dilewati.");
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $student->update($this->validatedData($request, $student));

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Student $student = null): array
    {
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
        ], [
            'nisn.unique' => 'NISN sudah digunakan siswa lain.',
            'birth_date.before' => 'Tanggal lahir harus valid dan sebelum hari ini.',
        ]);
    }

    private function parseCsvDate(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->toDateString();
            } catch (\Throwable) {
                //
            }
        }

        return null;
    }
}
