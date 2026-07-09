@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Buat Tryout" description="Pilih kelas, soal tryout dari master, dan jadwal pengerjaan." />

        <form method="POST" action="{{ route('guru.tryout.store') }}" class="mt-6 space-y-5">
            @csrf
            @include('guru.tryout.partials.form', [
                'tryout' => null,
                'kelas' => $kelas,
                'soal' => $soal,
                'locked' => false,
                'submit' => 'Simpan tryout',
            ])
        </form>
    </section>
</div>
@endsection
