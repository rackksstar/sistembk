@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() ? 'true' : 'false' }}, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Manajemen Data Siswa" description="CRUD siswa dengan validasi NISN unik dan tanggal lahir valid." />
            <button x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah siswa</button>
        </div>
        <x-alert class="mt-5" type="success" :message="session('success')" />

        <form method="GET" class="mt-6 grid gap-3 md:grid-cols-[1fr_auto]">
            <input name="search" value="{{ $search }}" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" placeholder="Cari nama, NISN, sekolah..." />
            <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Cari</button>
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Nama</th>
                            <th class="px-5 py-4">NISN</th>
                            <th class="px-5 py-4">Tanggal Lahir</th>
                            <th class="px-5 py-4">Sekolah</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($students as $student)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $student->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $student->nisn }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $student->birth_date->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $student->school ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button x-on:click="editOpen = {{ $student->id }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-xs font-semibold">Edit</button>
                                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Hapus siswa ini?')">
                                            @csrf @method('DELETE')
                                            <button class="rounded-2xl bg-red-600 px-4 py-2 text-xs font-semibold text-white">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-6"><x-empty-state title="Belum ada data siswa" description="Tambahkan data siswa pertama untuk mulai mengelola kelas bimbingan." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-5">{{ $students->links() }}</div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
            <x-section-title title="Tambah Siswa" description="Isi identitas siswa dengan NISN unik." />
            <form method="POST" action="{{ route('admin.students.store') }}" class="mt-6 space-y-4">
                @csrf
                @include('admin.students.partials.form', ['student' => null, 'studentUsers' => $studentUsers, 'submit' => 'Simpan siswa'])
            </form>
        </div>
    </div>

    @foreach($students as $student)
        <div x-show="editOpen === {{ $student->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Edit Siswa" description="Perbarui identitas siswa." />
                <form method="POST" action="{{ route('admin.students.update', $student) }}" class="mt-6 space-y-4">
                    @csrf @method('PUT')
                    @include('admin.students.partials.form', ['student' => $student, 'studentUsers' => $studentUsers, 'submit' => 'Update siswa'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
