<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\CounselorStudentService;
use App\Support\AngketProgress;
use App\Support\AngketQuestions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AngketController extends Controller
{
    public function __construct(
        private readonly CounselorStudentService $counselorStudentService
    ) {}

    public function index(Request $request): View
    {
        $activeSoalIds = AngketQuestions::activeIds();
        $totalSoalAktif = $activeSoalIds->count();

        $search = $request->string('search')->toString();

        $students = $this->counselorStudentService
            ->queryForCounselor(auth()->user())
            ->with(['user:id,name', 'kelas:id,nama,sekolah_id', 'kelas.sekolah:id,nama'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('kelas', fn ($k) => $k->where('nama', 'like', "%{$search}%"));
                });
            })
            ->withCount([
                'responsAngket as total_dijawab' => function ($q) use ($activeSoalIds) {
                    if ($activeSoalIds->isNotEmpty()) {
                        $q->whereIn('master_question_id', $activeSoalIds);
                    } else {
                        $q->whereRaw('0 = 1');
                    }
                },
            ])
            ->paginate(25)
            ->withQueryString();

        $students->getCollection()->transform(function ($student) use ($totalSoalAktif) {
            $student->predikat = AngketProgress::predikat(
                $student->total_dijawab,
                $totalSoalAktif
            );

            return $student;
        });

        return view('guru.angket.index', compact('students', 'totalSoalAktif', 'search'));
    }

    public function show(Student $student): View
    {
        abort_unless($this->counselorStudentService->canAccess($student, auth()->user()), 403);

        $student->load([
            'user',
            'kelas.sekolah',
            'responsAngket' => fn ($q) => $q
                ->with('masterQuestion:id,teks_pertanyaan,kategori')
                ->whereIn('master_question_id', AngketQuestions::activeIds())
                ->orderBy('master_question_id'),
        ]);

        $totalSoalAktif = AngketQuestions::activeCount();

        $predikat = AngketProgress::predikat($student->responsAngket->count(), $totalSoalAktif);

        return view('guru.angket.show', compact('student', 'predikat', 'totalSoalAktif'));
    }

    public function exportPdf(Student $student): Response
    {
        abort_unless($this->counselorStudentService->canAccess($student, auth()->user()), 403);

        $student->load([
            'user',
            'kelas.sekolah',
            'responsAngket' => fn ($q) => $q
                ->with('masterQuestion:id,teks_pertanyaan,kategori')
                ->whereIn('master_question_id', AngketQuestions::activeIds())
                ->orderBy('master_question_id'),
        ]);

        $totalSoalAktif = AngketQuestions::activeCount();

        $predikat = AngketProgress::predikat($student->responsAngket->count(), $totalSoalAktif);
        $tanggalCetak = now()->format('d M Y');

        $pdf = Pdf::loadView('guru.angket.pdf', compact(
            'student', 'predikat', 'totalSoalAktif', 'tanggalCetak'
        ))->setPaper('a4', 'portrait');

        $namaFile = 'angket-'.str($student->user?->name ?? $student->name ?? 'siswa')->slug().'-'.now()->format('Ymd').'.pdf';

        return $pdf->download($namaFile);
    }

}
