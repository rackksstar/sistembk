@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: {{ $errors->any() && ! $errors->has('csv_file') ? 'true' : 'false' }}, importOpen: {{ $errors->has('csv_file') ? 'true' : 'false' }}, editOpen: null }">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Data Login Siswa" description="Masukkan NISN dan tanggal lahir siswa agar siswa dapat login ke dashboard." />
            <div class="flex flex-wrap gap-3">
                <button x-on:click="importOpen = true" class="w-fit rounded-2xl border border-blue-200 bg-white px-5 py-3 text-sm font-semibold text-blue-700 hover:border-blue-300 hover:bg-blue-50">Import CSV</button>
                <button x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah siswa</button>
            </div>
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
                                        <form method="POST" action="{{ route('guru.students.destroy', $student) }}" onsubmit="return confirm('Hapus data siswa ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-2xl bg-red-600 px-4 py-2 text-xs font-semibold text-white">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6">
                                    <x-empty-state title="Belum ada data siswa" description="Tambahkan NISN dan tanggal lahir siswa agar siswa dapat login." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $students->links() }}</div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
            <x-section-title title="Tambah Data Siswa" description="Isi data yang akan dipakai siswa saat login." />
            <form method="POST" action="{{ route('guru.students.store') }}" class="mt-6 space-y-4">
                @csrf
                @include('guru.students.partials.form', ['student' => null, 'submit' => 'Simpan data siswa'])
            </form>
        </div>
    </div>

    <div x-show="importOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="importOpen = false" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
            <x-section-title title="Import CSV Siswa" description="Upload file CSV berisi nama, NISN, tanggal lahir, dan sekolah." />
            <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-700">
                Format header: <span class="font-semibold">nama, nisn, tanggal_lahir, sekolah</span>. Tanggal lahir dapat memakai format <span class="font-semibold">YYYY-MM-DD</span>, <span class="font-semibold">DD/MM/YYYY</span>, atau <span class="font-semibold">DD-MM-YYYY</span>.
            </div>
            <form method="POST" action="{{ route('guru.students.import') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div class="space-y-2">
                    <label for="csv-file" class="block text-sm font-semibold text-slate-900">File CSV</label>
                    <input id="csv-file" name="csv_file" type="file" accept=".csv,text/csv" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100" />
                    <x-input-error :messages="$errors->get('csv_file')" class="text-sm text-red-600" />
                </div>
                <button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-500">Upload dan import</button>
            </form>
        </div>
    </div>

    @foreach($students as $student)
        <div x-show="editOpen === {{ $student->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                <x-section-title title="Edit Data Siswa" description="Perbarui NISN dan tanggal lahir bila ada perubahan." />
                <form method="POST" action="{{ route('guru.students.update', $student) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    @include('guru.students.partials.form', ['student' => $student, 'submit' => 'Update data siswa'])
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
