@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-sm">
        <x-section-title title="Edit Tryout" :description="$tryout->judul" />
        <x-alert class="mt-4" type="error" :message="$errors->first('tryout')" />

        <form method="POST" action="{{ route('guru.tryout.update', $tryout) }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')
            @include('guru.tryout.partials.form', [
                'tryout' => $tryout,
                'kelas' => $kelas,
                'soal' => $soal,
                'locked' => $locked,
                'submit' => 'Perbarui tryout',
            ])
        </form>
    </section>
</div>
@endsection
