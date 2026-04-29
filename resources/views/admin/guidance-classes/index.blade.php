@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: false, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Kelas Bimbingan" description="Buat kelas dan tambahkan siswa ke kelompok bimbingan." />
            <button x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah kelas</button>
        </div>
        <x-alert class="mt-5" type="success" :message="session('success')" />
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        @forelse($classes as $class)
            <article class="rounded-3xl border border-blue-100 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">{{ $class->name }}</h3>
                        <p class="mt-2 inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-blue-700">Kode: {{ $class->code }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $class->description ?? 'Belum ada deskripsi.' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button x-on:click="editOpen = {{ $class->id }}" class="rounded-2xl border border-slate-200 px-3 py-2 text-xs font-semibold">Edit</button>
                        <form method="POST" action="{{ route('admin.guidance-classes.destroy', $class) }}" onsubmit="return confirm('Hapus kelas ini?')">
                            @csrf @method('DELETE')
                            <button class="rounded-2xl bg-red-600 px-3 py-2 text-xs font-semibold text-white">Delete</button>
                        </form>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.guidance-classes.students.attach', $class) }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                    @csrf
                    <select name="student_id" required class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        <option value="">Pilih siswa</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} - {{ $student->nisn }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Tambah</button>
                </form>

                <div class="mt-5 space-y-2">
                    @forelse($class->students as $student)
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-blue-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $student->name }}</p>
                                <p class="text-xs text-slate-500">NISN {{ $student->nisn }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.guidance-classes.students.detach', [$class, $student]) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs font-semibold text-red-600">Hapus</button>
                            </form>
                        </div>
                    @empty
                        <x-empty-state class="py-6" title="Belum ada siswa" description="Tambahkan siswa ke kelas ini lewat pilihan di atas." />
                    @endforelse
                </div>
            </article>
        @empty
            <div class="xl:col-span-2"><x-empty-state title="Belum ada kelas" description="Buat kelas bimbingan pertama untuk mengelompokkan siswa." /></div>
        @endforelse
    </section>

    <div>{{ $classes->links() }}</div>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
            <x-section-title title="Tambah Kelas" description="Buat kelas bimbingan baru." />
            <form method="POST" action="{{ route('admin.guidance-classes.store') }}" class="mt-6 space-y-4">
                @csrf
                @include('admin.guidance-classes.partials.form', ['class' => null, 'submit' => 'Simpan kelas'])
            </form>
        </div>
    </div>

    @foreach($classes as $class)
        <div x-show="editOpen === {{ $class->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Edit Kelas" description="Perbarui informasi kelas bimbingan." />
                <form method="POST" action="{{ route('admin.guidance-classes.update', $class) }}" class="mt-6 space-y-4">
                    @csrf @method('PUT')
                    @include('admin.guidance-classes.partials.form', ['class' => $class, 'submit' => 'Update kelas'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
