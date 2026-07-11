@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ formOpen: {{ $errors->any() ? 'true' : 'false' }} }">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title
                title="Pengajuan Konseling"
                description="Ajukan sesi bimbingan dan pantau status permintaan Anda."
            />
            <button
                type="button"
                x-on:click="formOpen = !formOpen"
                class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500"
            >
                <span x-text="formOpen ? 'Tutup formulir' : 'Ajukan konseling baru'"></span>
            </button>
        </div>
        <x-alert class="mt-5" type="success" :message="session('success')" />

        @if($upcoming->isNotEmpty())
            <div class="mt-6 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 bg-emerald-50 dark:bg-emerald-950/30 p-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">Jadwal mendatang</p>
                <ul class="mt-3 space-y-2">
                    @foreach($upcoming as $item)
                        <li class="flex flex-wrap items-center justify-between gap-2 text-sm">
                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $item->subject }}</span>
                            <span class="text-slate-600 dark:text-slate-400">
                                {{ $item->consultation_date->format('d M Y') }}
                                {{ substr($item->consultation_time, 0, 5) }}
                                · {{ $item->counselor?->name }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div x-show="formOpen" x-cloak class="mt-6 rounded-3xl border border-blue-100 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/30 p-6">
            <x-section-title title="Form pengajuan baru" description="Lengkapi topik, kategori, dan preferensi waktu." />
            <form action="{{ route('siswa.consultations.store') }}" method="POST" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                <div class="md:col-span-2 space-y-2">
                    <x-input-label for="subject" value="Topik / judul" />
                    <x-text-input id="subject" name="subject" class="w-full" :value="old('subject')" required placeholder="Contoh: Kesulitan belajar matematika" />
                    <x-input-error :messages="$errors->get('subject')" />
                </div>
                <div class="space-y-2">
                    <x-input-label for="case_category" value="Kategori masalah" />
                    <x-form-select id="case_category" name="case_category" required>
                        <option value="">Pilih kategori</option>
                        @foreach($caseCategories as $value => $label)
                            <option value="{{ $value }}" @selected(old('case_category') === $value)>{{ $label }}</option>
                        @endforeach
                    </x-form-select>
                    <x-input-error :messages="$errors->get('case_category')" />
                </div>
                <div class="space-y-2">
                    <x-input-label for="counselor_id" value="Guru BK" />
                    <x-form-select id="counselor_id" name="counselor_id" required>
                        <option value="">Pilih guru</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('counselor_id') == $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </x-form-select>
                    <x-input-error :messages="$errors->get('counselor_id')" />
                </div>
                <div class="space-y-2">
                    <x-input-label for="preferred_date" value="Preferensi tanggal (opsional)" />
                    <x-text-input id="preferred_date" type="date" name="preferred_date" class="w-full" :value="old('preferred_date')" />
                    <x-input-error :messages="$errors->get('preferred_date')" />
                </div>
                <div class="space-y-2">
                    <x-input-label for="preferred_time" value="Preferensi waktu (opsional)" />
                    <x-text-input id="preferred_time" name="preferred_time" class="w-full" :value="old('preferred_time')" placeholder="Contoh: Senin pagi / 10:00" />
                    <x-input-error :messages="$errors->get('preferred_time')" />
                </div>
                <div class="md:col-span-2 space-y-2">
                    <x-input-label for="details" value="Ceritakan keluhan" />
                    <textarea id="details" name="details" rows="4" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Apa yang ingin Anda bahas?">{{ old('details') }}</textarea>
                    <x-input-error :messages="$errors->get('details')" />
                </div>
                <div class="md:col-span-2">
                    <x-primary-button class="w-full sm:w-auto">Kirim pengajuan</x-primary-button>
                </div>
            </form>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <x-section-title title="Riwayat pengajuan" description="Semua permintaan konseling Anda." />
            <form method="GET" class="flex gap-2">
                <select name="status" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-sm">
                    <option value="">Semua status</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Filter</button>
            </form>
        </div>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Topik</th>
                            <th class="px-5 py-4">Kategori</th>
                            <th class="px-5 py-4">Guru BK</th>
                            <th class="px-5 py-4">Jadwal</th>
                            <th class="px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($consultations as $consultation)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $consultation->subject }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ $consultation->details }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $consultation->caseCategoryLabel() }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $consultation->counselor?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    @if($consultation->consultation_date)
                                        {{ $consultation->consultation_date->format('d M Y') }}
                                        {{ substr($consultation->consultation_time, 0, 5) }}
                                    @else
                                        {{ $consultation->preferred_time }}
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <x-status-badge :status="$consultation->status" />
                                    @if($consultation->status === 'ditolak' && $consultation->rejection_reason)
                                        <p class="mt-2 max-w-xs text-xs text-red-600 dark:text-red-300">{{ $consultation->rejection_reason }}</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8">
                                    <x-empty-state title="Belum ada pengajuan" description="Ajukan konseling pertama untuk memulai sesi bimbingan dengan Guru BK." />
                                    <div class="mt-4 text-center">
                                        <button type="button" x-on:click="formOpen = true" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Ajukan konseling baru</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-5">{{ $consultations->links() }}</div>
    </section>
</div>
@endsection
