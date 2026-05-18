@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Sosiometri" description="Pilih teman berdasarkan relasi sosial yang diminta. Data ini membantu Guru BK melihat pola hubungan siswa." />
        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-5" type="error" message="Periksa kembali pilihan teman Anda." />
        @endif
    </section>

    <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <form method="POST" action="{{ route('siswa.sociometry.store') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-5">
                <div class="space-y-2">
                    <x-input-label for="close_friend_id" value="Teman dekat" />
                    <x-form-select id="close_friend_id" name="close_friend_id" required>
                        <option value="">Pilih siswa</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" @selected(old('close_friend_id') == $student->id)>{{ $student->name }}</option>
                        @endforeach
                    </x-form-select>
                    @error('close_friend_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <x-input-label for="study_friend_id" value="Teman belajar / kerja kelompok" />
                    <x-form-select id="study_friend_id" name="study_friend_id" required>
                        <option value="">Pilih siswa</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" @selected(old('study_friend_id') == $student->id)>{{ $student->name }}</option>
                        @endforeach
                    </x-form-select>
                    @error('study_friend_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <x-input-label for="reason" value="Alasan singkat" />
                    <textarea id="reason" name="reason" rows="5" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('reason') }}</textarea>
                </div>

                <x-primary-button class="rounded-full px-6 py-3">Simpan Pilihan</x-primary-button>
            </div>
        </form>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title title="Pilihan Terakhir" description="Pilihan baru akan menggantikan pilihan sebelumnya." />
            <div class="mt-5 space-y-3">
                @forelse($responses as $response)
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ \App\Models\SociometryResponse::TYPES[$response->relation_type] ?? $response->relation_type }}</p>
                        <p class="mt-2 font-semibold text-slate-950">{{ $response->chosenStudent?->name }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada pilihan sosiometri.</p>
                @endforelse
            </div>
        </aside>
    </section>
</div>
@endsection
