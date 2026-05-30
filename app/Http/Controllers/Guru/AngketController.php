<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\MasterQuestion;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AngketController extends Controller
{
    public function index(Request $request): View
    {
        $totalSoalAktif = MasterQuestion::query()
            ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
            ->where('is_active', true)
            ->count();

        $students = Student::query()
            ->with(['user', 'kelas'])
            ->withCount([
                'responsAngket as total_dijawab' => function ($q) {
                    $q->whereHas('masterQuestion', fn ($mq) => $mq
                        ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
                        ->where('is_active', true)
                    );
                },
            ])
            ->paginate(25);

        $students->getCollection()->transform(function ($student) use ($totalSoalAktif) {
            $student->predikat = $this->hitungPredikat(
                $student->total_dijawab,
                $totalSoalAktif
            );

            return $student;
        });

        return view('guru.angket.index', compact('students', 'totalSoalAktif'));
    }

    public function show(Student $student): View
    {
        $student->load([
            'user',
            'kelas',
            'responsAngket' => fn ($q) => $q
                ->with('masterQuestion')
                ->whereHas('masterQuestion', fn ($mq) => $mq
                    ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
                )
                ->orderBy('master_question_id'),
        ]);

        $totalSoalAktif = MasterQuestion::query()
            ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
            ->where('is_active', true)
            ->count();

        $predikat = $this->hitungPredikat(
            $student->responsAngket->count(),
            $totalSoalAktif
        );

        return view('guru.angket.show', compact('student', 'predikat', 'totalSoalAktif'));
    }

    public function exportPdf(Student $student): Response
    {
        $student->load([
            'user',
            'kelas',
            'responsAngket' => fn ($q) => $q
                ->with('masterQuestion')
                ->whereHas('masterQuestion', fn ($mq) => $mq
                    ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
                )
                ->orderBy('master_question_id'),
        ]);

        $totalSoalAktif = MasterQuestion::query()
            ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
            ->where('is_active', true)
            ->count();

        $predikat = $this->hitungPredikat($student->responsAngket->count(), $totalSoalAktif);
        $tanggalCetak = now()->format('d M Y');

        $pdf = Pdf::loadView('guru.angket.pdf', compact(
            'student', 'predikat', 'totalSoalAktif', 'tanggalCetak'
        ))->setPaper('a4', 'portrait');

        $namaFile = 'angket-'.str($student->user?->name ?? $student->name ?? 'siswa')->slug().'-'.now()->format('Ymd').'.pdf';

        return $pdf->download($namaFile);
    }

    public function hitungPredikat(int $dijawab, int $total): string
    {
        if ($total === 0) {
            return 'Belum Ada Soal';
        }

        $persen = ($dijawab / $total) * 100;

        return match (true) {
            $persen >= 80 => 'Lengkap',
            $persen >= 50 => 'Sebagian',
            default => 'Belum Lengkap',
        };
    }
}
