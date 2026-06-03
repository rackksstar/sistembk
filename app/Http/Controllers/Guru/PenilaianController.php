<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenilaianController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = $request->integer('bulan', now()->month);
        $tahun = $request->integer('tahun', now()->year);

        $konseling = ConsultationRequest::query()
            ->where('counselor_id', auth()->id())
            ->where('status', ConsultationRequest::STATUS_SELESAI)
            ->whereMonth('scheduled_at', $bulan)
            ->whereYear('scheduled_at', $tahun)
            ->with([
                'penilaianPelayanan:id,consultation_request_id,skor_materi,skor_cara,skor_manfaat,catatan',
                'student:id,name',
                'student.studentProfile:id,user_id,kelas_id',
                'student.studentProfile.kelas:id,nama',
            ])
            ->latest('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        $dinilai = $konseling->getCollection()
            ->filter(fn ($k) => $k->penilaianPelayanan !== null);

        $summary = [
            'total_konseling' => $konseling->total(),
            'total_dinilai' => $dinilai->count(),
            'rata_materi' => round($dinilai->avg(fn ($k) => $k->penilaianPelayanan->skor_materi) ?? 0, 1),
            'rata_cara' => round($dinilai->avg(fn ($k) => $k->penilaianPelayanan->skor_cara) ?? 0, 1),
            'rata_manfaat' => round($dinilai->avg(fn ($k) => $k->penilaianPelayanan->skor_manfaat) ?? 0, 1),
        ];

        $summary['rata_overall'] = round(
            ($summary['rata_materi'] + $summary['rata_cara'] + $summary['rata_manfaat']) / 3,
            1
        );

        return view('guru.penilaian.index', compact('konseling', 'summary', 'bulan', 'tahun'));
    }
}
