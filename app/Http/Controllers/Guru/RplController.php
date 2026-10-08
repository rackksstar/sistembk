<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Rpl;
use App\Models\SchoolClass;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RplController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $classId = $request->integer('class_id') ?: null;
        $semester = $request->integer('semester') ?: null;
        $year = $request->integer('year') ?: null;
        $sort = $request->string('sort', 'baru')->toString();

        $rpls = Rpl::query()
            ->with(['classRoom:id,name', 'student:id,name,class_id', 'groupStudents:id,name,class_id'])
            ->withCount('consultationReports')
            ->where('teacher_id', auth()->id())
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($classId, fn ($query) => $query->where('class_id', $classId))
            ->when($semester, fn ($query) => $query->where('semester', $semester))
            ->when($year, fn ($query) => $query->where('year', $year))
            ->when($sort === 'lama', fn ($query) => $query->oldest(), fn ($query) => $query->latest())
            ->paginate(10)
            ->withQueryString();

        $classes = SchoolClass::query()->orderBy('name')->get(['id', 'name']);
        $students = User::query()
            ->where('role', User::ROLE_SISWA)
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get(['id', 'name', 'class_id']);

        return view('guru.rpls.index', [
            'rpls' => $rpls,
            'types' => Rpl::TYPES,
            'statuses' => Rpl::STATUSES,
            'classes' => $classes,
            'students' => $students,
            'type' => $type,
            'status' => $status,
            'classId' => $classId,
            'semester' => $semester,
            'year' => $year,
            'sort' => $sort,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $studentIds = $data['group_student_ids'] ?? [];
        unset($data['group_student_ids']);

        $rpl = Rpl::create($data + ['teacher_id' => auth()->id()]);
        $rpl->groupStudents()->sync($studentIds);

        return back()->with('success', 'RPL berhasil dibuat.');
    }

    public function update(Request $request, Rpl $rpl): RedirectResponse
    {
        abort_unless($rpl->teacher_id === auth()->id(), 403);

        $data = $this->validatedData($request);
        $studentIds = $data['group_student_ids'] ?? [];
        unset($data['group_student_ids']);

        $rpl->update($data);
        $rpl->groupStudents()->sync($studentIds);

        return back()->with('success', 'RPL berhasil diperbarui.');
    }

    public function destroy(Rpl $rpl): RedirectResponse
    {
        abort_unless($rpl->teacher_id === auth()->id(), 403);

        $rpl->delete();

        return back()->with('success', 'RPL berhasil dihapus.');
    }

    public function print(Rpl $rpl)
    {
        abort_unless($rpl->teacher_id === auth()->id(), 403);

        $rpl->load(['teacher.schoolModel', 'classRoom', 'student', 'groupStudents']);
        $school = $rpl->teacher?->schoolModel;
        $logoPath = public_path('img/logo_sekolah.png');

        return Pdf::loadView('guru.rpls.print', [
                'rpl' => $rpl,
                'schoolName' => trim((string) ($school?->name ?? $rpl->teacher?->school ?? '')) ?: 'Nama Sekolah',
                'schoolAddress' => trim((string) ($school?->address ?? '')) ?: '-',
                // Logo dikirim sebagai data URI base64 karena dompdf tidak selalu
                // bisa memuat file lewat URL biasa.
                'logoDataUri' => is_file($logoPath)
                    ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
                    : null,
            ])
            ->setPaper('a4')
            ->stream('rpl-'.$rpl->id.'.pdf');
    }

    /**
     * Validasi form RPL. Aturannya bergantung tipe RPL: individu wajib punya
     * student_id, kelompok wajib punya group_student_ids (min. 2 siswa), dan
     * field pelaksanaan (pertemuan, durasi, topik, tempat) wajib untuk kelompok.
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Rpl::TYPES))],
            'class_id' => ['required', 'exists:classes,id'],
            'student_id' => [
                'nullable',
                'required_if:type,'.Rpl::TYPE_INDIVIDU,
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', User::ROLE_SISWA)
                    ->where('status', User::STATUS_APPROVED)
                    ->where('class_id', $request->input('class_id'))),
            ],
            'group_student_ids' => ['nullable', 'required_if:type,'.Rpl::TYPE_KELOMPOK, 'array', 'min:2'],
            'group_student_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', User::ROLE_SISWA)
                    ->where('status', User::STATUS_APPROVED)
                    ->where('class_id', $request->input('class_id'))),
            ],
            'status' => ['required', Rule::in(array_keys(Rpl::STATUSES))],
            'semester' => ['required', 'integer', 'in:1,2'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'service_date' => ['nullable', 'date'],
            'meeting_number' => ['nullable', 'required_if:type,'.Rpl::TYPE_KELOMPOK, 'integer', 'min:1', 'max:100'],
            'duration_minutes' => ['nullable', 'required_if:type,'.Rpl::TYPE_KELOMPOK, 'integer', 'min:1', 'max:600'],
            'topik_permasalahan' => ['nullable', 'required_if:type,'.Rpl::TYPE_KELOMPOK, 'string', 'max:255'],
            'location' => ['nullable', 'required_if:type,'.Rpl::TYPE_KELOMPOK, 'string', 'max:255'],
            'media' => ['nullable', 'string', 'max:1000'],
            'target' => ['nullable', 'string', 'max:255'],
            'tujuan' => ['required', 'string', 'max:3000'],
            'materi' => ['required', 'string', 'max:3000'],
            'metode' => ['required', 'string', 'max:3000'],
            'evaluasi' => ['required', 'string', 'max:3000'],
        ], [
            'group_student_ids.required_if' => 'Pilih minimal 2 siswa untuk RPL kelompok.',
            'group_student_ids.array' => 'Daftar anggota kelompok harus valid.',
            'group_student_ids.min' => 'Kelompok harus terdiri dari minimal 2 siswa.',
            'group_student_ids.*.exists' => 'Setiap siswa harus berasal dari kelas yang sama dengan RPL.',
            'student_id.exists' => 'Siswa harus berasal dari kelas yang sama dengan RPL.',
            'meeting_number.required_if' => 'Nomor pertemuan wajib diisi untuk RPL kelompok.',
            'duration_minutes.required_if' => 'Durasi layanan wajib diisi untuk RPL kelompok.',
            'topik_permasalahan.required_if' => 'Topik permasalahan wajib diisi untuk RPL kelompok.',
            'location.required_if' => 'Tempat pelaksanaan wajib diisi untuk RPL kelompok.',
        ]);

        // Field khusus kelompok dibersihkan untuk RPL individu supaya
        // data keduanya tidak tercampur di database.
        if ($validated['type'] === Rpl::TYPE_INDIVIDU) {
            $validated['group_student_ids'] = [];
            $validated['topik_permasalahan'] = null;
        } else {
            $validated['student_id'] = null;
        }

        if (blank($validated['target'] ?? '')) {
            $validated['target'] = $this->defaultTarget($validated);
        }

        return $validated;
    }

    private function defaultTarget(array $data): string
    {
        if ($data['type'] === Rpl::TYPE_INDIVIDU && $data['student_id']) {
            return User::find($data['student_id'])?->name ?? 'Siswa';
        }

        $count = count($data['group_student_ids'] ?? []);

        return $count.' siswa';
    }
}
