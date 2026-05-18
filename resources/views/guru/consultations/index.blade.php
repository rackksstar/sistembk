@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ detailOpen: null, scheduleOpen: null, reportOpen: null, rejectOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Kalender Jadwal Konseling" description="Visualisasi sesi yang sudah dijadwalkan." />
        <div id="consultation-calendar" class="mt-6 min-h-[420px] rounded-2xl border border-slate-100 bg-slate-50/50 p-2"></div>
        @if($upcomingWeek->isNotEmpty())
            <div class="mt-6">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Minggu ini</p>
                <ul class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($upcomingWeek as $slot)
                        <li class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm">
                            <span class="font-semibold text-slate-900">{{ $slot->student?->name }}</span>
                            <span class="mt-1 block text-slate-600">{{ $slot->consultation_date->format('d M') }} · {{ substr($slot->consultation_time, 0, 5) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Pengajuan & Approval Konseling" description="Setujui, tolak, jadwalkan, dan isi laporan konseling." />
            <form method="GET" class="flex gap-3">
                <select name="status" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <option value="">Semua status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Filter</button>
            </form>
        </div>
        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-3" type="error" :message="$errors->first()" />
        @endif

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Siswa</th>
                            <th class="px-5 py-4">Topik</th>
                            <th class="px-5 py-4">Kategori</th>
                            <th class="px-5 py-4">Jadwal</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($consultations as $consultation)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $consultation->student?->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $consultation->subject }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $consultation->caseCategoryLabel() }}</td>
                                <td class="px-5 py-4 text-slate-600">
                                    @if($consultation->consultation_date)
                                        {{ $consultation->consultation_date->format('d M Y') }} {{ substr($consultation->consultation_time, 0, 5) }}
                                    @else
                                        {{ $consultation->preferred_time }}
                                    @endif
                                </td>
                                <td class="px-5 py-4"><x-status-badge :status="$consultation->status" /></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button type="button" x-on:click="detailOpen = {{ $consultation->id }}" class="rounded-2xl border border-slate-200 px-3 py-2 text-xs font-semibold">Detail</button>
                                        @if($consultation->status === 'pending')
                                            <form method="POST" action="{{ route('guru.consultations.approve', $consultation) }}">
                                                @csrf @method('PATCH')
                                                <button class="rounded-2xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Setujui</button>
                                            </form>
                                            <button type="button" x-on:click="rejectOpen = {{ $consultation->id }}" class="rounded-2xl bg-red-600 px-3 py-2 text-xs font-semibold text-white">Tolak</button>
                                        @endif
                                        @if($consultation->canBeRejected() && $consultation->status !== 'pending')
                                            <button type="button" x-on:click="rejectOpen = {{ $consultation->id }}" class="rounded-2xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-700">Tolak</button>
                                        @endif
                                        @if($consultation->isSchedulable())
                                            <button type="button" x-on:click="scheduleOpen = {{ $consultation->id }}" class="rounded-2xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Jadwal</button>
                                        @endif
                                        @if(in_array($consultation->status, ['disetujui', 'dijadwalkan_ulang']))
                                            <button type="button" x-on:click="reportOpen = {{ $consultation->id }}" class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Laporan</button>
                                        @endif
                                        @if($consultation->result)
                                            <a href="{{ route('guru.consultations.print', $consultation) }}" target="_blank" class="rounded-2xl bg-white px-3 py-2 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">PDF</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-6"><x-empty-state title="Belum ada pengajuan" description="Pengajuan konseling siswa akan muncul di tabel ini." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-5">{{ $consultations->links() }}</div>
    </section>

    @foreach($consultations as $consultation)
        <div x-show="detailOpen === {{ $consultation->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="detailOpen = null" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Detail Konseling" :description="$consultation->subject" />
                <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="font-semibold">Siswa</dt><dd class="mt-1 text-slate-600">{{ $consultation->student?->name }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="font-semibold">Status</dt><dd class="mt-1"><x-status-badge :status="$consultation->status" /></dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="font-semibold">Kategori</dt><dd class="mt-1 text-slate-600">{{ $consultation->caseCategoryLabel() }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><dt class="font-semibold">Preferensi</dt><dd class="mt-1 text-slate-600">{{ $consultation->preferred_time }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2"><dt class="font-semibold">Detail siswa</dt><dd class="mt-1 text-slate-600">{{ $consultation->details ?? '-' }}</dd></div>
                    @if($consultation->rejection_reason)
                        <div class="rounded-2xl bg-red-50 p-4 sm:col-span-2"><dt class="font-semibold text-red-800">Alasan ditolak</dt><dd class="mt-1 text-red-700">{{ $consultation->rejection_reason }}</dd></div>
                    @endif
                    <div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2"><dt class="font-semibold">Hasil</dt><dd class="mt-1 text-slate-600">{{ $consultation->result ?? '-' }}</dd></div>
                </dl>
            </div>
        </div>

        <div x-show="rejectOpen === {{ $consultation->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="rejectOpen = null" class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Tolak pengajuan" description="Berikan alasan agar siswa memahami keputusan." />
                <form method="POST" action="{{ route('guru.consultations.reject', $consultation) }}" class="mt-6 space-y-4">
                    @csrf @method('PATCH')
                    <textarea name="rejection_reason" rows="4" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Alasan penolakan"></textarea>
                    <button class="w-full rounded-2xl bg-red-600 px-4 py-3 text-sm font-semibold text-white">Tolak pengajuan</button>
                </form>
            </div>
        </div>

        <div x-show="scheduleOpen === {{ $consultation->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="scheduleOpen = null" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Penjadwalan Konseling" description="Sistem mengecek bentrok jadwal otomatis." />
                <form method="POST" action="{{ route('guru.consultations.schedule', $consultation) }}" class="mt-6 space-y-4">
                    @csrf @method('PATCH')
                    <input type="date" name="consultation_date" value="{{ old('consultation_date', $consultation->consultation_date?->format('Y-m-d')) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                    <input type="time" name="consultation_time" value="{{ old('consultation_time', $consultation->consultation_time ? substr($consultation->consultation_time, 0, 5) : '') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                    <select name="student_id" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" @selected($consultation->student_id === $student->id)>{{ $student->name }}</option>
                        @endforeach
                    </select>
                    <textarea name="notes" rows="3" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Catatan jadwal">{{ old('notes', $consultation->notes) }}</textarea>
                    <button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white">Simpan jadwal</button>
                </form>
            </div>
        </div>

        <div x-show="reportOpen === {{ $consultation->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="reportOpen = null" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Laporan Konseling" description="Isi hasil konseling dan evaluasi sesi." />
                <form method="POST" action="{{ route('guru.consultations.report', $consultation) }}" class="mt-6 space-y-4">
                    @csrf @method('PATCH')
                    <select name="case_category" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        <option value="">Pilih kategori kasus</option>
                        @foreach($caseCategories as $value => $label)
                            <option value="{{ $value }}" @selected(old('case_category', $consultation->case_category) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <textarea name="result" rows="4" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Hasil konseling">{{ old('result', $consultation->result) }}</textarea>
                    <textarea name="evaluation" rows="4" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Evaluasi">{{ old('evaluation', $consultation->evaluation) }}</textarea>
                    <textarea name="follow_up" rows="3" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Tindak lanjut">{{ old('follow_up', $consultation->follow_up) }}</textarea>
                    <button class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white">Simpan laporan</button>
                </form>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('consultation-calendar');
    if (!el || typeof FullCalendar === 'undefined') return;

    const calendar = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        locale: 'id',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek',
        },
        events: '{{ route('guru.consultations.events') }}',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    });
    calendar.render();
});
</script>
@endpush
@endsection
