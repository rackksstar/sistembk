<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\PenilaianPelayanan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenilaianController extends Controller
{
    public function index(Request $request): View
    {
        $bulan = $request->integer('bulan', now()->month);
        $tahun = $request->integer('tahun', now()->year);

        $baseQuery = ConsultationRequest::query()
            ->where('counselor_id', auth()->id())
            ->where('status', ConsultationRequest::STATUS_SELESAI);
        $this->applyPeriodFilter($baseQuery, $bulan, $tahun);

        $konseling = (clone $baseQuery)
            ->with([
                'penilaianPelayanan:id,consultation_request_id,skor_materi,skor_cara,skor_manfaat,catatan',
                'student:id,name',
                'student.studentProfile:id,user_id,kelas_id',
                'student.studentProfile.kelas:id,nama',
            ])
            ->orderByRaw('COALESCE(scheduled_at, consultation_date, updated_at) DESC')
            ->paginate(20)
            ->withQueryString();

        $aggregates = PenilaianPelayanan::query()
            ->whereHas('consultationRequest', function ($query) use ($bulan, $tahun) {
                $query->where('counselor_id', auth()->id())
                    ->where('status', ConsultationRequest::STATUS_SELESAI);
                $this->applyPeriodFilter($query, $bulan, $tahun);
            })
            ->selectRaw('count(*) as total_dinilai')
            ->selectRaw('avg(skor_materi) as rata_materi')
            ->selectRaw('avg(skor_cara) as rata_cara')
            ->selectRaw('avg(skor_manfaat) as rata_manfaat')
            ->first();

        $summary = [
            'total_konseling' => (clone $baseQuery)->count(),
            'total_dinilai' => (int) ($aggregates->total_dinilai ?? 0),
            'rata_materi' => round((float) ($aggregates->rata_materi ?? 0), 1),
            'rata_cara' => round((float) ($aggregates->rata_cara ?? 0), 1),
            'rata_manfaat' => round((float) ($aggregates->rata_manfaat ?? 0), 1),
        ];

        $summary['rata_overall'] = round(
            ($summary['rata_materi'] + $summary['rata_cara'] + $summary['rata_manfaat']) / 3,
            1
        );

        return view('guru.penilaian.index', compact('konseling', 'summary', 'bulan', 'tahun'));
    }

    /**
     * Filter periode memakai scheduled_at, fallback consultation_date, lalu updated_at
     * agar sesi selesai tanpa jadwal tetap muncul.
     */
    private function applyPeriodFilter(Builder $query, int $bulan, int $tahun): void
    {
        $query->where(function (Builder $q) use ($bulan, $tahun) {
            $q->where(function (Builder $inner) use ($bulan, $tahun) {
                $inner->whereNotNull('scheduled_at')
                    ->whereMonth('scheduled_at', $bulan)
                    ->whereYear('scheduled_at', $tahun);
            })->orWhere(function (Builder $inner) use ($bulan, $tahun) {
                $inner->whereNull('scheduled_at')
                    ->whereNotNull('consultation_date')
                    ->whereMonth('consultation_date', $bulan)
                    ->whereYear('consultation_date', $tahun);
            })->orWhere(function (Builder $inner) use ($bulan, $tahun) {
                $inner->whereNull('scheduled_at')
                    ->whereNull('consultation_date')
                    ->whereMonth('updated_at', $bulan)
                    ->whereYear('updated_at', $tahun);
            });
        });
    }
}
