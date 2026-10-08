<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdiConsultationRequest;
use App\Models\ConsultationRequest;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Layanan Konsultasi Prodi Kuliah — catatan dosen (Ar).
 * Memakai tabel consultation_requests yang sudah ada (case_category=prodi_kuliah).
 */
class ProdiConsultationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $user = $request->user();

        $consultations = ConsultationRequest::query()
            ->with(['counselor:id,name', 'programStudi:id,nama,jenjang_pendidikan,institusi'])
            ->where('student_id', $user->id)
            ->where('case_category', ConsultationRequest::CASE_PRODI_KULIAH)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('siswa.konsultasi-prodi.index', [
            'consultations' => $consultations,
            'teachers' => $this->availableCounselors($user),
            'programStudis' => ProgramStudi::query()
                ->active()
                ->where('is_verified', true)
                ->orderBy('nama')
                ->get(['id', 'nama', 'jenjang_pendidikan', 'institusi']),
            'status' => $status,
            'statuses' => ConsultationRequest::filterableStatuses(),
            'prefillProdiId' => $request->integer('program_studi_id') ?: null,
            'prefillSubject' => $request->string('subject')->toString() ?: null,
        ]);
    }

    public function store(StoreProdiConsultationRequest $request): RedirectResponse
    {
        $prodi = ProgramStudi::query()
            ->active()
            ->where('is_verified', true)
            ->findOrFail($request->validated('program_studi_id'));

        $preferredDate = $request->validated('preferred_date');
        $preferredTime = $request->validated('preferred_time');

        $created = ConsultationRequest::query()->create([
            'student_id' => $request->user()->id,
            'counselor_id' => $request->validated('counselor_id'),
            'program_studi_id' => $prodi->id,
            'subject' => $request->validated('subject'),
            'case_category' => ConsultationRequest::CASE_PRODI_KULIAH,
            'preferred_date' => $preferredDate,
            'preferred_time' => $preferredTime
                ?: ($preferredDate ? 'Sesuai tanggal pilihan' : 'Fleksibel — menunggu jadwal Guru BK'),
            'details' => $request->validated('details'),
            'status' => ConsultationRequest::STATUS_PENDING,
        ]);

        ActivityLogger::log('consultation.prodi.submitted', $created, [
            'program_studi_id' => $prodi->id,
            'program_studi' => $prodi->nama,
        ]);

        return redirect()
            ->route('siswa.konsultasi-prodi.index')
            ->with('success', 'Pengajuan konsultasi prodi kuliah berhasil dikirim. Guru BK akan meninjau permintaan Anda.');
    }

    /**
     * Prioritaskan Guru BK sekolah siswa; fallback semua guru approved.
     *
     * @return Collection<int, User>
     */
    private function availableCounselors(User $student): Collection
    {
        $student->loadMissing('studentProfile.kelas');
        $sekolahId = $student->studentProfile?->kelas?->sekolah_id;

        $query = User::query()
            ->where('role', User::ROLE_GURU)
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name');

        if ($sekolahId) {
            $sameSchool = (clone $query)
                ->whereHas('guruBkProfile', fn ($q) => $q->where('sekolah_id', $sekolahId))
                ->get(['id', 'name']);

            if ($sameSchool->isNotEmpty()) {
                return $sameSchool;
            }
        }

        return $query->get(['id', 'name']);
    }
}
