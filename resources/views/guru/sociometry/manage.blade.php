@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Kelola Instrumen Sosiometri" description="Aktifkan atau nonaktifkan instrumen sosiometri per kelas." />
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Kelas</th>
                        <th class="px-5 py-3">Total Siswa</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($classes as $kelas)
                        @php $instrument = $instruments[$kelas->id] ?? null; @endphp
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $kelas->nama }}</td>
                            <td class="px-5 py-4 text-slate-700 dark:text-slate-300">{{ $kelas->students->count() }}</td>
                            <td class="px-5 py-4 text-slate-700 dark:text-slate-300">{{ $instrument && $instrument->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ route('guru.sociometry.manage.toggle') }}">
                                    @csrf
                                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}" />
                                    <input type="hidden" name="is_active" value="{{ $instrument && $instrument->is_active ? 0 : 1 }}" />
                                    <button class="rounded-lg px-3 py-2 text-sm font-semibold {{ $instrument && $instrument->is_active ? 'bg-rose-600 text-white' : 'bg-green-600 text-white' }}">
                                        {{ $instrument && $instrument->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
