<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\PenilaianPelayanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenilaianController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->studentProfile;
        abort_unless($student, 404);

        $konseling = ConsultationRequest::query()
            ->where('student_id', auth()->id())
            ->where('status', ConsultationRequest::STATUS_SELESAI)
            ->with(['penilaianPelayanan', 'counselor:id,name'])
            ->latest('scheduled_at')
            ->paginate(15);

        return view('siswa.penilaian.index', compact('konseling'));
    }

    public function create(Request $request): View
    {
        $student = auth()->user()->studentProfile;
        abort_unless($student, 404);

        $konseling = ConsultationRequest::query()
            ->where('id', $request->query('consultation'))
            ->where('student_id', auth()->id())
            ->where('status', ConsultationRequest::STATUS_SELESAI)
            ->firstOrFail();

        $sudahDinilai = PenilaianPelayanan::query()
            ->where('consultation_request_id', $konseling->id)
            ->where('student_id', $student->id)
            ->exists();

        abort_if($sudahDinilai, 403, 'Kamu sudah memberikan penilaian untuk konseling ini.');

        return view('siswa.penilaian.create', compact('konseling'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'consultation_request_id' => ['required', 'integer', 'exists:consultation_requests,id'],
            'skor_materi' => ['required', 'integer', 'min:1', 'max:5'],
            'skor_cara' => ['required', 'integer', 'min:1', 'max:5'],
            'skor_manfaat' => ['required', 'integer', 'min:1', 'max:5'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $student = auth()->user()->studentProfile;
        abort_unless($student, 404);

        $konseling = ConsultationRequest::query()
            ->where('id', $validated['consultation_request_id'])
            ->where('student_id', auth()->id())
            ->where('status', ConsultationRequest::STATUS_SELESAI)
            ->firstOrFail();

        abort_if(
            PenilaianPelayanan::query()
                ->where('consultation_request_id', $konseling->id)
                ->where('student_id', $student->id)
                ->exists(),
            403,
            'Penilaian sudah diberikan.'
        );

        PenilaianPelayanan::create([
            'consultation_request_id' => $konseling->id,
            'student_id' => $student->id,
            'skor_materi' => $validated['skor_materi'],
            'skor_cara' => $validated['skor_cara'],
            'skor_manfaat' => $validated['skor_manfaat'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()
            ->route('siswa.penilaian.index')
            ->with('success', 'Terima kasih! Penilaianmu sudah disimpan.');
    }
}
