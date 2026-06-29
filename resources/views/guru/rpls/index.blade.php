@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="RPL" description="Susun dan kelola RPL layanan individu maupun kelompok." />
            <button type="button" x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Tambah RPL</button>
        </div>
        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-5" type="error" message="Periksa kembali data RPL." />
        @endif

        <form method="GET" action="{{ route('guru.rpls.index') }}" class="mt-6 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
            <x-form-select name="type">
                <option value="">Semua jenis</option>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                @endforeach
            </x-form-select>
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Filter</button>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse($rpls as $rpl)
            <article class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <span class="rounded-full bg-blue-50 dark:bg-blue-950/40 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-blue-700 dark:text-blue-300">{{ $rpl->typeLabel() }}</span>
                        <h3 class="mt-4 text-lg font-bold text-slate-950 dark:text-white">{{ $rpl->title }}</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $rpl->target ?: 'Sasaran belum diisi' }} - {{ $rpl->service_date?->format('d M Y') ?: 'Tanggal fleksibel' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('guru.rpls.print', $rpl) }}" target="_blank" class="rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700">Cetak PDF</a>
                        <button type="button" x-on:click="editOpen = {{ $rpl->id }}" class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60">Edit</button>
                    </div>
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><p class="font-semibold text-slate-900 dark:text-slate-100">Tujuan</p><p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $rpl->tujuan }}</p></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><p class="font-semibold text-slate-900 dark:text-slate-100">Materi</p><p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $rpl->materi }}</p></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><p class="font-semibold text-slate-900 dark:text-slate-100">Metode</p><p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $rpl->metode }}</p></div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 p-4"><p class="font-semibold text-slate-900 dark:text-slate-100">Evaluasi</p><p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $rpl->evaluasi }}</p></div>
                </div>
                <form method="POST" action="{{ route('guru.rpls.destroy', $rpl) }}" class="mt-4" onsubmit="return confirm('Hapus RPL ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Hapus RPL</button>
                </form>
            </article>
        @empty
            <div class="xl:col-span-2">
                <x-empty-state title="Belum ada RPL" description="Buat RPL pertama untuk layanan individu atau kelompok." />
            </div>
        @endforelse
    </section>

    {{ $rpls->links() }}

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <x-section-title title="Tambah RPL" description="Lengkapi komponen layanan BK." />
                <button type="button" x-on:click="createOpen = false" class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-sm font-semibold text-slate-600 dark:text-slate-400">x</button>
            </div>
            <form method="POST" action="{{ route('guru.rpls.store') }}" class="mt-6">
                @csrf
                @include('guru.rpls.partials.form', ['rpl' => null, 'submit' => 'Simpan RPL'])
            </form>
        </div>
    </div>

    @foreach($rpls as $rpl)
        <div x-show="editOpen === {{ $rpl->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <x-section-title title="Edit RPL" description="Perbarui rencana layanan." />
                    <button type="button" x-on:click="editOpen = null" class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-sm font-semibold text-slate-600 dark:text-slate-400">x</button>
                </div>
                <form method="POST" action="{{ route('guru.rpls.update', $rpl) }}" class="mt-6">
                    @csrf
                    @method('PUT')
                    @include('guru.rpls.partials.form', ['rpl' => $rpl, 'submit' => 'Update RPL'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
