@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    createOpen: {{ $errors->any() && ! $errors->has('import_file') && old('form_context') !== 'edit' ? 'true' : 'false' }},
    importOpen: {{ $errors->has('import_file') || session('import_errors') ? 'true' : 'false' }},
    editOpen: {{ $errors->any() && old('form_context') === 'edit' ? (int) old('editing_id') : 'null' }}
}">
    <section class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 shadow-xs">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title title="Manajemen Data Siswa" description="CRUD siswa dengan validasi NISN unik dan tanggal lahir valid." />
            <div class="flex flex-wrap items-center gap-3">
                <button x-on:click="createOpen = true" class="w-fit rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah siswa</button>
                <button x-on:click="importOpen = true" class="w-fit rounded-2xl border border-blue-200 dark:border-blue-800 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-blue-700 dark:text-blue-300 hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-950/40">Import Excel/CSV</button>
            </div>
        </div>
        <x-alert class="mt-5" type="success" :message="session('success')" />

        <form method="GET" class="mt-6 grid gap-3 md:grid-cols-[1fr_1fr_auto_auto]">
            <input name="search" value="{{ $search }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" placeholder="Cari nama, NISN, sekolah..." />
            <select name="kelas_id" class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm">
                <option value="">Semua kelas</option>
                @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->id }}" @selected($kelasId === $kelas->id)>
                        {{ $kelas->nama }} ({{ $kelas->sekolah?->nama ?? 'Sekolah' }})
                    </option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Filter</button>
            <x-filter-reset />
        </form>

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Nama</th>
                            <th class="px-5 py-4">NISN</th>
                            <th class="px-5 py-4">Tanggal Lahir</th>
                            <th class="px-5 py-4">Kelas</th>
                            <th class="px-5 py-4">Sekolah</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        @forelse($students as $student)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $student->name }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $student->nisn }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $student->birth_date->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $student->kelas?->nama ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $student->kelas?->sekolah?->nama ?? $student->school ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button x-on:click="editOpen = {{ $student->id }}" class="rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-xs font-semibold">Edit</button>
                                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Hapus siswa ini?')">
                                            @csrf @method('DELETE')
                                            <button class="rounded-2xl bg-red-600 px-4 py-2 text-xs font-semibold text-white">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-6">
                                <x-empty-state title="Belum ada data siswa" description="Tambahkan data siswa pertama untuk mulai mengelola kelas bimbingan." />
                                <div class="mt-4 text-center">
                                    <button type="button" x-on:click="createOpen = true" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">Tambah siswa</button>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-5">{{ $students->links() }}</div>
    </section>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="createOpen = false" class="w-full max-w-xl max-h-[calc(100vh-2rem)] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
            <x-section-title title="Tambah Siswa" description="Isi identitas siswa dengan NISN unik." />
            <form method="POST" action="{{ route('admin.students.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="form_context" value="create">
                @include('admin.students.partials.form', ['student' => null, 'studentUsers' => $studentUsers, 'kelasList' => $kelasList, 'submit' => 'Simpan siswa'])
            </form>
        </div>
    </div>

    @foreach($students as $student)
        <div x-show="editOpen === {{ $student->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <div x-on:click.outside="editOpen = null" class="w-full max-w-xl max-h-[calc(100vh-2rem)] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
                <x-section-title title="Edit Siswa" description="Perbarui identitas siswa." />
                <form method="POST" action="{{ route('admin.students.update', $student) }}" class="mt-6 space-y-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="form_context" value="edit">
                    <input type="hidden" name="editing_id" value="{{ $student->id }}">
                    @include('admin.students.partials.form', ['student' => $student, 'studentUsers' => $studentUsers, 'kelasList' => $kelasList, 'submit' => 'Update siswa'])
                </form>
            </div>
        </div>
    @endforeach

    <div x-show="importOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
        <div x-on:click.outside="importOpen = false" class="w-full max-w-xl max-h-[calc(100vh-2rem)] overflow-y-auto rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl">
            <x-section-title title="Import Siswa" description="Upload file CSV atau Excel berisi data siswa." />
            <div class="mt-5 rounded-2xl border border-blue-100 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/40 p-4 text-sm leading-6 text-blue-700 dark:text-blue-300">
                <p><span class="font-semibold">Langkah singkat:</span> unduh template Excel (sudah ada dropdown seluruh kelas + ID), isi datanya, lalu upload kembali.</p>
                <p class="mt-2">Header: <span class="font-semibold">nama, nisn, tanggal_lahir, sekolah, kelas</span>. Pada kolom <span class="font-semibold">kelas</span> cukup pilih dari dropdown (bentuk <span class="font-semibold">Nama Kelas (ID)</span>). Format tanggal <span class="font-semibold">DD/MM/YYYY</span>, <span class="font-semibold">YYYY-MM-DD</span>, atau <span class="font-semibold">DD-MM-YYYY</span>. Semua kelas tersedia; baris yang kosong atau tidak valid akan dilewati.</p>
                <div class="mt-3">
                    <a href="{{ route('admin.students.template') }}" class="inline-flex items-center rounded-2xl bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-800 px-4 py-2 text-sm font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-950/40">Unduh template Excel</a>
                </div>
            </div>
            @if (session('import_errors'))
                <div class="mt-4 rounded-2xl border border-red-200 dark:border-red-900/60 bg-red-50 dark:bg-red-950/40 p-4 text-sm leading-6 text-red-700 dark:text-red-300">
                    <p class="font-semibold">Beberapa baris dilewati:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach (session('import_errors') as $pesanBaris)
                            <li>{{ $pesanBaris }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div class="space-y-2">
                    <label for="import-file-admin" class="block text-sm font-semibold text-slate-900 dark:text-slate-100">File CSV atau Excel</label>
                    <input id="import-file-admin" name="import_file" type="file" accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 text-sm focus:border-blue-400 focus:outline-hidden focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/50" />
                    <x-input-error :messages="$errors->get('import_file')" class="text-sm text-red-600" />
                </div>
                <button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-500">Upload dan import</button>
            </form>
        </div>
    </div>
</div>
@endsection
