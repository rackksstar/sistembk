@extends('layouts.app')

@section('content')
@php
    $friends = $classmates->map(fn ($friend) => ['id' => $friend->id, 'name' => $friend->name])->values();
@endphp

<div
    class="space-y-6"
    x-data="{
        step: 1,
        friends: @js($friends),
        closestQuery: '',
        studyQuery: '',
        closestId: '{{ old('closest_friend_id') }}',
        studyId: '{{ old('study_friend_id') }}',
        closestOpen: false,
        studyOpen: false,
        filtered(query) {
            return this.friends.filter((friend) => friend.name.toLowerCase().includes(query.toLowerCase())).slice(0, 6);
        },
        choose(type, friend) {
            if (type === 'closest') {
                this.closestId = friend.id;
                this.closestQuery = friend.name;
                this.closestOpen = false;
            } else {
                this.studyId = friend.id;
                this.studyQuery = friend.name;
                this.studyOpen = false;
            }
        }
    }"
>
    <section class="overflow-hidden rounded-3xl border border-white bg-[linear-gradient(135deg,#f8fbff_0%,#edf5ff_45%,#dbe7f5_100%)] p-6 shadow-lg shadow-blue-100/60 sm:p-8">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-center">
            <div>
                <p class="inline-flex rounded-full border border-blue-100 bg-white/85 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-blue-600 shadow-sm">
                    Asesmen Konseling
                </p>
                <h1 class="mt-5 max-w-3xl text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">
                    Asesmen Mandiri Minat Bakat dan Sosiometri
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">
                    Jawaban Anda membantu Guru BK memahami potensi, kebutuhan belajar, dan hubungan sosial di kelas. Isi dengan tenang dan jujur.
                </p>
            </div>

            <div class="rounded-3xl border border-white bg-white/75 p-5 shadow-md shadow-blue-100/70 backdrop-blur">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Status Pengisian</p>
                @if($latestAssessment)
                    <p class="mt-3 text-lg font-bold text-slate-950">Sudah terkirim</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Terakhir dikirim pada {{ $latestAssessment->submitted_at?->format('d M Y H:i') }}.</p>
                @else
                    <p class="mt-3 text-lg font-bold text-slate-950">Belum ada data</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Mulai dari Langkah 1 lalu lanjutkan ke Sosiometri.</p>
                @endif
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="grid gap-3 sm:grid-cols-2">
            <button type="button" x-on:click="step = 1" class="flex items-center gap-3 rounded-2xl border px-4 py-3 text-left transition" :class="step === 1 ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold" :class="step === 1 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'">1</span>
                <span>
                    <span class="block text-sm font-bold">Minat Bakat</span>
                    <span class="block text-xs">Potensi, mapel, dan cita-cita</span>
                </span>
            </button>
            <button type="button" x-on:click="step = 2" class="flex items-center gap-3 rounded-2xl border px-4 py-3 text-left transition" :class="step === 2 ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold" :class="step === 2 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'">2</span>
                <span>
                    <span class="block text-sm font-bold">Sosiometri</span>
                    <span class="block text-xs">Pilihan teman dan alasan</span>
                </span>
            </button>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />

        @if($errors->any())
            <x-alert class="mt-5" type="error" message="Periksa kembali form. Ada data yang belum sesuai." />
        @endif

        <form action="{{ route('siswa.assessments.store') }}" method="POST" class="mt-6">
            @csrf

            <div x-show="step === 1" x-transition.opacity class="grid gap-5 lg:grid-cols-2">
                <div class="space-y-2">
                    <x-input-label for="interest_area" value="Bidang yang paling diminati" />
                    <x-form-select id="interest_area" name="interest_area" required>
                        <option value="">Pilih bidang</option>
                        @foreach(['Sains dan teknologi', 'Sosial dan komunikasi', 'Seni dan desain', 'Bisnis dan kewirausahaan', 'Kesehatan', 'Olahraga'] as $area)
                            <option value="{{ $area }}" @selected(old('interest_area') === $area)>{{ $area }}</option>
                        @endforeach
                    </x-form-select>
                    @error('interest_area')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <x-input-label for="favorite_subject" value="Mata pelajaran yang paling disukai" />
                    <x-text-input id="favorite_subject" name="favorite_subject" value="{{ old('favorite_subject') }}" required placeholder="Contoh: Matematika, Bahasa Indonesia" />
                    @error('favorite_subject')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2 lg:col-span-2">
                    <x-input-label for="strengths" value="Kekuatan diri yang Anda rasakan" />
                    <textarea id="strengths" name="strengths" rows="5" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Tuliskan kemampuan, kebiasaan baik, atau aktivitas yang membuat Anda percaya diri.">{{ old('strengths') }}</textarea>
                    @error('strengths')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2 lg:col-span-2">
                    <x-input-label for="career_goal" value="Cita-cita atau rencana karier" />
                    <x-text-input id="career_goal" name="career_goal" value="{{ old('career_goal') }}" placeholder="Opsional, contoh: psikolog, programmer, wirausaha" />
                    @error('career_goal')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end lg:col-span-2">
                    <button type="button" x-on:click="step = 2" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Lanjut ke Sosiometri
                    </button>
                </div>
            </div>

            <div x-show="step === 2" x-transition.opacity class="grid gap-5 lg:grid-cols-2">
                <div class="relative space-y-2">
                    <x-input-label for="closest_friend_search" value="Teman terdekat" />
                    <input type="hidden" name="closest_friend_id" x-bind:value="closestId">
                    <x-text-input id="closest_friend_search" type="search" x-model="closestQuery" x-on:focus="closestOpen = true" x-on:input="closestOpen = true" required autocomplete="off" placeholder="Cari nama teman..." />
                    <div x-show="closestOpen" x-on:click.outside="closestOpen = false" class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-200/70" x-cloak>
                        <template x-for="friend in filtered(closestQuery)" :key="friend.id">
                            <button type="button" x-on:click="choose('closest', friend)" class="block w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700" x-text="friend.name"></button>
                        </template>
                    </div>
                    @error('closest_friend_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="relative space-y-2">
                    <x-input-label for="study_friend_search" value="Teman pilihan untuk belajar/kerja kelompok" />
                    <input type="hidden" name="study_friend_id" x-bind:value="studyId">
                    <x-text-input id="study_friend_search" type="search" x-model="studyQuery" x-on:focus="studyOpen = true" x-on:input="studyOpen = true" required autocomplete="off" placeholder="Cari nama teman..." />
                    <div x-show="studyOpen" x-on:click.outside="studyOpen = false" class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl shadow-slate-200/70" x-cloak>
                        <template x-for="friend in filtered(studyQuery)" :key="friend.id">
                            <button type="button" x-on:click="choose('study', friend)" class="block w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700" x-text="friend.name"></button>
                        </template>
                    </div>
                    @error('study_friend_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2 lg:col-span-2">
                    <x-input-label for="social_reason" value="Alasan pilihan sosial" />
                    <textarea id="social_reason" name="social_reason" rows="5" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Ceritakan singkat mengapa Anda memilih teman tersebut.">{{ old('social_reason') }}</textarea>
                    @error('social_reason')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between lg:col-span-2">
                    <button type="button" x-on:click="step = 1" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                        Kembali
                    </button>
                    <x-primary-button class="rounded-full">Kirim Asesmen</x-primary-button>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection
