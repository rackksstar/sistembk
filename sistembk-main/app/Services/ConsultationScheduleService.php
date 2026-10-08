<?php

namespace App\Services;

use App\Models\ConsultationRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ConsultationScheduleService
{
    /** @var list<string> */
    public const ACTIVE_SLOT_STATUSES = [
        ConsultationRequest::STATUS_APPROVED,
        ConsultationRequest::STATUS_RESCHEDULED,
    ];

    public function hasConflict(
        int $counselorId,
        string $date,
        string $time,
        ?int $excludeConsultationId = null
    ): bool {
        return ConsultationRequest::query()
            ->where('counselor_id', $counselorId)
            ->whereIn('status', self::ACTIVE_SLOT_STATUSES)
            ->whereDate('consultation_date', $date)
            ->where('consultation_time', $this->normalizeTime($time))
            ->when($excludeConsultationId, fn ($query) => $query->where('id', '!=', $excludeConsultationId))
            ->exists();
    }

    public function scheduledAt(string $date, string $time): Carbon
    {
        return Carbon::parse($date.' '.$this->normalizeTime($time));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function calendarEventsForCounselor(int $counselorId): Collection
    {
        return ConsultationRequest::query()
            ->with('student:id,name')
            ->where('counselor_id', $counselorId)
            ->whereIn('status', [
                ConsultationRequest::STATUS_APPROVED,
                ConsultationRequest::STATUS_RESCHEDULED,
                ConsultationRequest::STATUS_SELESAI,
            ])
            ->whereNotNull('consultation_date')
            ->whereNotNull('consultation_time')
            ->get()
            ->map(fn (ConsultationRequest $consultation) => [
                'id' => $consultation->id,
                'title' => ($consultation->student?->name ?? 'Siswa').' — '.$consultation->subject,
                'start' => $consultation->consultation_date->format('Y-m-d').'T'.$this->normalizeTime($consultation->consultation_time),
                'end' => $consultation->consultation_date->format('Y-m-d').'T'.$this->normalizeTime($consultation->consultation_time),
                'backgroundColor' => match ($consultation->status) {
                    ConsultationRequest::STATUS_SELESAI => '#3b82f6',
                    ConsultationRequest::STATUS_RESCHEDULED => '#f59e0b',
                    default => '#10b981',
                },
                'borderColor' => 'transparent',
                'extendedProps' => [
                    'status' => $consultation->status,
                    'statusLabel' => $consultation->statusLabel(),
                ],
            ]);
    }

    private function normalizeTime(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }
}
