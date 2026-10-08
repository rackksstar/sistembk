@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    detailOpen: null,
    scheduleOpen: {{ $errors->any() && old('modal_action') === 'schedule' ? (int) old('modal_consultation_id') : 'null' }},
    reportOpen: {{ $errors->any() && old('modal_action') === 'report' ? (int) old('modal_consultation_id') : 'null' }},
    rejectOpen: {{ $errors->any() && old('modal_action') === 'reject' ? (int) old('modal_consultation_id') : 'null' }}
}">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <x-section-title title="Kalender Jadwal Konseling" description="Visualisasi sesi yang sudah dijadwalkan." />
        <div id="consultation-calendar" class="mt-6 min-h-[420px] rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 p-2"></div>
        @if($upcomingWeek->isNotEmpty())
            <div class="mt-6">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Minggu ini</p>
                <ul class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($upcomingWeek as $schedule)
                        <li class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $schedule->student?->name ?? 'Siswa dihapus' }}</span>
                            <span class="mt-1 block text-slate-600 dark:text-slate-400">
                                {{ $schedule->consultation_date?->format('d M') ?? 'Belum dijadwalkan' }}@if($schedule->consultation_time) · {{ substr($schedule->consultation_time, 0, 5) }}@endif · {{ $schedule->caseCategoryLabel() }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <x-section-title title="Pengajuan & Approval Konseling" description="Setujui, tolak, jadwalkan, dan isi laporan konseling." />
        <form method="GET" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_190px_190px_auto]">
            <div class="min-w-0">
                <label for="search" class="sr-only">Cari konseling</label>
                <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Cari topik, siswa, NISN, atau guru BK..."
                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm placeholder:text-slate-400 focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
            </div>
            <div class="min-w-0">
                <label for="filter-status" class="sr-only">Filter status</label>
                <select id="filter-status" name="status" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                    <option value="">Semua status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label for="filter-kategori" class="sr-only">Filter kategori</label>
                <select id="filter-kategori" name="kategori" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                    <option value="">Semua kategori</option>
                    @foreach($caseCategories as $value => $label)
                        <option value="{{ $value }}" @selected($kategori === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 sm:col-span-2 lg:col-span-1">
                <button class="flex-1 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Filter</button>
                <x-filter-reset />
            </div>
        </form>
        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-3" type="error" :message="$errors->first()" />
        @endif

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Siswa</th>
                            <th class="px-5 py-4">Kelas</th>
                            <th class="px-5 py-4">Topik</th>
                            <th class="px-5 py-4">Kategori</th>
                            <th class="px-5 py-4">RPL Terkait</th>
                            <th class="px-5 py-4">Jadwal</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($consultations as $consultation)
                            <tr>
                                <td class="px-5 py-4">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $consultation->student?->name }}</span>
                                    @if($consultation->counselor_id)
                                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Guru: {{ $consultation->counselor?->name ?? '—' }}</span>
                                    @else
                                        <span class="mt-0.5 block text-xs text-amber-600 dark:text-amber-400">Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $consultation->student?->studentProfile?->kelas?->nama ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ $consultation->subject }}</span>
                                    @if($consultation->details)
                                        <span class="mt-0.5 block line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ $consultation->details }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $consultation->caseCategoryLabel() }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    @if($consultation->rpl)
                                        <span class="line-clamp-2">{{ $consultation->rpl->title }}</span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500">-</span>
                                    @endif
                                </td>
                                @if($consultation->consultation_date)
                                    <td class="px-5 py-4">
                                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $consultation->consultation_date->format('d M Y') }}</span>
                                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $consultation->consultation_time ? substr($consultation->consultation_time, 0, 5) : '-' }}</span>
                                    </td>
                                @else
                                    <td class="px-5 py-4">
                                        <span class="font-medium text-slate-900 dark:text-slate-100">{{ $consultation->preferred_time ?? '-' }}</span>
                                        @if($consultation->preferred_date)
                                            <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">{{ $consultation->preferred_date->format('d M Y') }}</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-5 py-4"><x-status-badge :status="$consultation->status" /></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button type="button" x-on:click="detailOpen = {{ $consultation->id }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">Detail</button>
                                        @if($consultation->status === 'pending')
                                            <form method="POST" action="{{ route('guru.consultations.approve', $consultation) }}">
                                                @csrf @method('PATCH')
                                                <button class="rounded-2xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Setujui</button>
                                            </form>
                                            <button type="button" x-on:click="rejectOpen = {{ $consultation->id }}" class="rounded-2xl bg-red-600 px-3 py-2 text-xs font-semibold text-white">Tolak</button>
                                        @endif
                                        @if($consultation->canBeRejected() && $consultation->status !== 'pending')
                                            <button type="button" x-on:click="rejectOpen = {{ $consultation->id }}" class="rounded-2xl border border-red-200 dark:border-red-800 px-3 py-2 text-xs font-semibold text-red-700 dark:text-red-300">Tolak</button>
                                        @endif
                                        @if($consultation->isSchedulable())
                                            <button type="button" x-on:click="scheduleOpen = {{ $consultation->id }}" class="rounded-2xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white">Jadwal</button>
                                        @endif
                                        @if(in_array($consultation->status, ['disetujui', 'dijadwalkan_ulang']))
                                            <button type="button" x-on:click="reportOpen = {{ $consultation->id }}" class="rounded-2xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-500">Laporan</button>
                                        @endif
                                        @if($consultation->result)
                                            <a href="{{ route('guru.consultations.print', $consultation) }}" target="_blank" class="rounded-2xl bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold text-blue-700 dark:text-blue-300 ring-1 ring-blue-100 dark:ring-blue-900/50">PDF</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-6"><x-empty-state title="Belum ada pengajuan" description="Pengajuan konseling siswa akan muncul di tabel ini." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-5">{{ $consultations->links() }}</div>
    </section>

    @foreach($consultations as $consultation)
        <div x-show="detailOpen === {{ $consultation->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="detailOpen = null" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Detail Konseling" :description="$consultation->subject" />
                <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Siswa</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->student?->name }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Kelas</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->student?->studentProfile?->kelas?->nama ?? '—' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Status</dt><dd class="mt-1"><x-status-badge :status="$consultation->status" /></dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Kategori</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->caseCategoryLabel() }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">RPL Terkait</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->rpl?->title ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Semester / Tahun</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">Semester {{ $consultation->semester ?? '-' }} / {{ $consultation->academicYearLabel() }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Pertemuan ke-</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->meeting_number ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Durasi</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->duration_minutes ? $consultation->duration_minutes.' menit' : '-' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Tempat</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->location ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Guru BK</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->counselor?->name ?? 'Belum ditugaskan' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Preferensi siswa</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->preferred_time ?? '-' }}{{ $consultation->preferred_date ? ' · '.$consultation->preferred_date->format('d M Y') : '' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Jadwal sesi</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->consultation_date?->format('d M Y') ?? 'Belum dijadwalkan' }}{{ $consultation->consultation_time ? ' · '.substr($consultation->consultation_time, 0, 5) : '' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><dt class="font-semibold">Diajukan</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->created_at?->format('d M Y H:i') }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 sm:col-span-2"><dt class="font-semibold">Detail siswa</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->details ?? '-' }}</dd></div>
                    @if($consultation->notes)
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 sm:col-span-2"><dt class="font-semibold">Catatan jadwal</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->notes }}</dd></div>
                    @endif
                    @if($consultation->rejection_reason)
                        <div class="rounded-2xl bg-red-50 dark:bg-red-950/40 p-4 sm:col-span-2"><dt class="font-semibold text-red-800">Alasan ditolak</dt><dd class="mt-1 text-red-700 dark:text-red-300">{{ $consultation->rejection_reason }}</dd></div>
                    @endif
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 sm:col-span-2"><dt class="font-semibold">Pendekatan dan teknik konseling</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->approach_technique ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 sm:col-span-2"><dt class="font-semibold">Hasil</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->result ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 sm:col-span-2"><dt class="font-semibold">Evaluasi</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->evaluation ?? '-' }}</dd></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4 sm:col-span-2"><dt class="font-semibold">Tindak lanjut</dt><dd class="mt-1 text-slate-600 dark:text-slate-400">{{ $consultation->follow_up ?? '-' }}</dd></div>
                </dl>
            </div>
        </div>

        <div x-show="rejectOpen === {{ $consultation->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="rejectOpen = null" class="w-full max-w-lg rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Tolak pengajuan" description="Berikan alasan agar siswa memahami keputusan." />
                <form method="POST" action="{{ route('guru.consultations.reject', $consultation) }}" class="mt-6 space-y-4">
                    @csrf @method('PATCH')
                    <input type="hidden" name="modal_action" value="reject">
                    <input type="hidden" name="modal_consultation_id" value="{{ $consultation->id }}">
                    <textarea name="rejection_reason" rows="4" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Alasan penolakan">{{ old('rejection_reason') }}</textarea>
                    <x-input-error :messages="$errors->get('rejection_reason')" />
                    <button class="w-full rounded-2xl bg-red-600 px-4 py-3 text-sm font-semibold text-white">Tolak pengajuan</button>
                </form>
            </div>
        </div>

        <div x-show="scheduleOpen === {{ $consultation->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="scheduleOpen = null" class="w-full max-w-xl rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Penjadwalan Konseling" description="Sistem mengecek bentrok jadwal otomatis." />
                <form method="POST" action="{{ route('guru.consultations.schedule', $consultation) }}" class="mt-6 space-y-4">
                    @csrf @method('PATCH')
                    <input type="hidden" name="modal_action" value="schedule">
                    <input type="hidden" name="modal_consultation_id" value="{{ $consultation->id }}">
                    <input type="date" name="consultation_date" value="{{ old('consultation_date', $consultation->consultation_date?->format('Y-m-d')) }}" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" />
                    <x-input-error :messages="$errors->get('consultation_date')" />
                    <input type="time" name="consultation_time" value="{{ old('consultation_time', $consultation->consultation_time ? substr($consultation->consultation_time, 0, 5) : '') }}" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" />
                    <x-input-error :messages="$errors->get('consultation_time')" />
                    <input type="hidden" name="student_id" value="{{ $consultation->student_id }}">
                    <p class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                        Siswa: <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $consultation->student?->name ?? '—' }}</span>
                    </p>
                    <textarea name="notes" rows="3" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Catatan jadwal">{{ old('notes', $consultation->notes) }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" />
                    <button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white">Simpan jadwal</button>
                </form>
            </div>
        </div>

        <div x-show="reportOpen === {{ $consultation->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="reportOpen = null" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Laporan Konseling" description="Isi detail pelaksanaan, hasil, dan evaluasi sesi." />
                <form method="POST" action="{{ route('guru.consultations.report', $consultation) }}" class="mt-6 space-y-4">
                    @csrf @method('PATCH')
                    <input type="hidden" name="modal_action" value="report">
                    <input type="hidden" name="modal_consultation_id" value="{{ $consultation->id }}">
                    <select name="rpl_id" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                        <option value="">Tidak dikaitkan dengan RPL</option>
                        @php
                            $linkableRpls = $linkableRpls->filter(function ($rpl) use ($consultation) {
                                if ($rpl->type === \App\Models\Rpl::TYPE_INDIVIDU) {
                                    return (int) $rpl->student_id === (int) $consultation->student_id;
                                }
                                // Kelompok: cocok lewat kelas siswa atau keanggotaan kelompok.
                                $studentClassId = (int) ($consultation->student?->class_id ?? 0);
                                return ($studentClassId > 0 && (int) $rpl->class_id === $studentClassId)
                                    || $rpl->groupStudents->contains('id', $consultation->student_id);
                            });
                        @endphp
                        @foreach($linkableRpls as $rpl)
                            <option value="{{ $rpl->id }}" @selected((string) old('rpl_id', $consultation->rpl_id) === (string) $rpl->id)>
                                [{{ $rpl->typeLabel() }}] {{ $rpl->title }} - {{ $rpl->classRoom?->name ?? 'Tanpa kelas' }}
                            </option>
                        @endforeach
                    </select>
                    <select name="case_category" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                        <option value="">Pilih kategori kasus</option>
                        @foreach($caseCategories as $value => $label)
                            <option value="{{ $value }}" @selected(old('case_category', $consultation->case_category) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('case_category')" />
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="meeting_number_{{ $consultation->id }}" value="Pertemuan ke-" />
                            <x-text-input id="meeting_number_{{ $consultation->id }}" name="meeting_number" type="number" min="1" max="100" value="{{ old('meeting_number', $consultation->meeting_number) }}" class="mt-1 w-full" placeholder="mis. 1" />
                            <x-input-error :messages="$errors->get('meeting_number')" />
                        </div>
                        <div>
                            <x-input-label for="duration_minutes_{{ $consultation->id }}" value="Waktu (durasi menit)" />
                            <x-text-input id="duration_minutes_{{ $consultation->id }}" name="duration_minutes" type="number" min="1" max="600" value="{{ old('duration_minutes', $consultation->duration_minutes) }}" class="mt-1 w-full" placeholder="mis. 45" />
                            <x-input-error :messages="$errors->get('duration_minutes')" />
                        </div>
                        <div>
                            <x-input-label for="location_{{ $consultation->id }}" value="Tempat" />
                            <x-text-input id="location_{{ $consultation->id }}" name="location" value="{{ old('location', $consultation->location) }}" class="mt-1 w-full" placeholder="mis. Ruang BK" />
                            <x-input-error :messages="$errors->get('location')" />
                        </div>
                        <div>
                            <x-input-label for="semester_{{ $consultation->id }}" value="Semester" />
                            <select id="semester_{{ $consultation->id }}" name="semester" class="mt-1 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                                <option value="1" @selected((string) old('semester', $consultation->semester ?? 1) === '1')>Semester 1</option>
                                <option value="2" @selected((string) old('semester', $consultation->semester ?? 1) === '2')>Semester 2</option>
                            </select>
                            <x-input-error :messages="$errors->get('semester')" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="year_{{ $consultation->id }}" value="Tahun Pelajaran" />
                            <x-text-input id="year_{{ $consultation->id }}" name="year" type="number" min="2020" max="2100" value="{{ old('year', $consultation->year ?? now()->year) }}" class="mt-1 w-full" />
                            <x-input-error :messages="$errors->get('year')" />
                        </div>
                    </div>
                    <textarea name="approach_technique" rows="3" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Pendekatan dan teknik konseling yang digunakan">{{ old('approach_technique', $consultation->approach_technique) }}</textarea>
                    <x-input-error :messages="$errors->get('approach_technique')" />
                    <textarea name="result" rows="4" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Hasil konseling">{{ old('result', $consultation->result) }}</textarea>
                    <x-input-error :messages="$errors->get('result')" />
                    <textarea name="evaluation" rows="4" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Evaluasi">{{ old('evaluation', $consultation->evaluation) }}</textarea>
                    <x-input-error :messages="$errors->get('evaluation')" />
                    <textarea name="follow_up" rows="3" class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm" placeholder="Tindak lanjut">{{ old('follow_up', $consultation->follow_up) }}</textarea>
                    <x-input-error :messages="$errors->get('follow_up')" />
                    <button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-500">Simpan laporan</button>
                </form>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<style>
    html.dark #consultation-calendar {
        --fc-page-bg-color: transparent;
        --fc-neutral-bg-color: rgb(30 41 59 / 0.65);
        --fc-neutral-text-color: rgb(203 213 225);
        --fc-border-color: rgb(51 65 85);
        --fc-button-text-color: #fff;
        --fc-button-bg-color: rgb(37 99 235);
        --fc-button-border-color: rgb(37 99 235);
        --fc-button-hover-bg-color: rgb(29 78 216);
        --fc-button-hover-border-color: rgb(29 78 216);
        --fc-button-active-bg-color: rgb(30 64 175);
        --fc-button-active-border-color: rgb(30 64 175);
        --fc-event-bg-color: rgb(37 99 235);
        --fc-event-border-color: rgb(37 99 235);
        --fc-event-text-color: #fff;
        --fc-today-bg-color: rgb(30 58 138 / 0.35);
        --fc-list-event-hover-bg-color: rgb(51 65 85 / 0.5);
        --fc-more-link-bg-color: rgb(51 65 85);
        --fc-more-link-text-color: rgb(226 232 240);
        color: rgb(226 232 240);
    }

    html.dark #consultation-calendar .fc-theme-standard td,
    html.dark #consultation-calendar .fc-theme-standard th,
    html.dark #consultation-calendar .fc-scrollgrid {
        border-color: rgb(51 65 85);
    }

    html.dark #consultation-calendar .fc-col-header-cell-cushion,
    html.dark #consultation-calendar .fc-daygrid-day-number,
    html.dark #consultation-calendar .fc-list-day-text,
    html.dark #consultation-calendar .fc-list-day-side-text,
    html.dark #consultation-calendar .fc-toolbar-title {
        color: rgb(226 232 240);
    }
</style>
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
