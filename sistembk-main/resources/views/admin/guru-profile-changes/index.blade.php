@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title
                title="Perubahan Profil Guru"
                description="Pantau perubahan nama, no HP, dan NIP yang dilakukan Guru BK dari halaman profil."
            />

            <form method="GET" action="{{ route('admin.guru-profile-changes.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <select name="status" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50">
                    <option value="baru" @selected($status === 'baru')>Belum dibaca</option>
                    <option value="dibaca" @selected($status === 'dibaca')>Sudah dibaca</option>
                    <option value="semua" @selected($status === 'semua')>Semua</option>
                </select>
                <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition">Filter</button>
            </form>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Guru BK</th>
                            <th class="px-5 py-4">Sebelum</th>
                            <th class="px-5 py-4">Sesudah</th>
                            <th class="px-5 py-4">Waktu</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($changes as $change)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $change->user?->name ?? '-' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $change->user?->guruBkProfile?->sekolah?->nama ?? $change->user?->school ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    <p>Nama: {{ $change->old_values['name'] ?? '-' }}</p>
                                    <p>No HP: {{ $change->old_values['no_hp'] ?? '-' }}</p>
                                    <p>NIP: {{ $change->old_values['nip'] ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">
                                    <p>Nama: {{ $change->new_values['name'] ?? '-' }}</p>
                                    <p>No HP: {{ $change->new_values['no_hp'] ?? '-' }}</p>
                                    <p>NIP: {{ $change->new_values['nip'] ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $change->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] ring-1 {{ $change->reviewed_at ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 ring-emerald-200' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 ring-amber-200' }}">
                                        {{ $change->reviewed_at ? 'Dibaca' : 'Baru' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end">
                                        @if(! $change->reviewed_at)
                                            <form method="POST" action="{{ route('admin.guru-profile-changes.reviewed', $change) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="rounded-2xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-500">Tandai dibaca</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $change->reviewed_at->format('d M Y H:i') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6">
                                    <x-empty-state title="Belum ada perubahan" description="Perubahan profil Guru BK akan muncul di sini setelah guru mengubah profilnya." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $changes->links() }}
        </div>
    </section>
</div>
@endsection
