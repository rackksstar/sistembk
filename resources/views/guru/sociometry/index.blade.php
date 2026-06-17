@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Peta Sosiometri" description="Ringkasan pilihan teman untuk melihat siswa populer dan siswa yang belum mendapat pilihan." />
    </section>

    @if(isset($classSummaries))
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Rekap Per Kelas" description="Total siswa, sudah mengisi, dan belum mengisi sosiometri." />
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Kelas</th>
                        <th class="px-5 py-3">Total Siswa</th>
                        <th class="px-5 py-3">Sudah Mengisi</th>
                        <th class="px-5 py-3">Belum Mengisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($classSummaries as $c)
                        <tr>
                            <td class="px-5 py-3 font-semibold">{{ $c['nama'] }}</td>
                            <td class="px-5 py-3">{{ $c['total_students'] }}</td>
                            <td class="px-5 py-3 text-green-600 font-bold">{{ $c['filled'] }}</td>
                            <td class="px-5 py-3 text-rose-600">{{ $c['not_filled'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif

    <section class="grid gap-5 md:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title title="Siswa Populer" description="Urutan berdasarkan jumlah dipilih." />
            <div class="mt-5 space-y-3">
                @forelse($popular as $student)
                    <div class="flex items-center justify-between rounded-2xl bg-blue-50 p-4">
                        <a href="{{ route('guru.sociometry.show', $student->id) }}" class="font-semibold text-slate-950">{{ $student->name }}</a>
                        <span class="rounded-full bg-blue-600 px-3 py-1 text-sm font-bold text-white">{{ $student->received_sociometry_choices_count }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada pilihan masuk.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title title="Siswa Terisolasi" description="Siswa yang belum dipilih oleh teman lain." />
            <div class="mt-5 flex flex-wrap gap-2">
                @forelse($isolated as $student)
                    <a href="{{ route('guru.sociometry.show', $student->id) }}" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">{{ $student->name }}</a>
                @empty
                    <p class="text-sm text-slate-500">Semua siswa sudah mendapat minimal satu pilihan.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 p-6">
            <div class="flex items-start justify-between gap-4">
                <x-section-title title="Relasi Sosiometri" description="Daftar hubungan yang dikirim siswa." />

                @if(isset($classSummaries))
                    <form method="GET" class="flex items-center gap-2">
                        <select name="kelas_id" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($classSummaries as $c)
                                <option value="{{ $c['id'] }}" {{ (isset($kelasId) && $kelasId == $c['id']) ? 'selected' : '' }}>{{ $c['nama'] }}</option>
                            @endforeach
                        </select>

                        <select name="filled" class="rounded-lg border px-3 py-2 text-sm">
                            <option value="all" {{ (isset($filterStatus) && $filterStatus === 'all') ? 'selected' : '' }}>Semua</option>
                            <option value="filled" {{ (isset($filterStatus) && $filterStatus === 'filled') ? 'selected' : '' }}>Sudah Mengisi</option>
                            <option value="not_filled" {{ (isset($filterStatus) && $filterStatus === 'not_filled') ? 'selected' : '' }}>Belum Mengisi</option>
                        </select>

                        <button class="rounded-lg bg-blue-600 px-3 py-2 text-sm text-white">Filter</button>
                        <a href="{{ route('guru.sociometry.index') }}" class="text-sm text-slate-500">Clear</a>
                    </form>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Pemilih</th>
                        <th class="px-5 py-4">Dipilih</th>
                        <th class="px-5 py-4">Relasi</th>
                        <th class="px-5 py-4">Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if(isset($filterStatus) && $filterStatus === 'not_filled')
                        @if($notFilledStudents->isNotEmpty())
                            @foreach($notFilledStudents as $nf)
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-slate-900">{{ $nf->name }}</td>
                                    <td class="px-5 py-4" colspan="3">Belum mengisi sosiometri</td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="4" class="px-5 py-8"><x-empty-state title="Semua sudah mengisi" description="Tidak ada siswa yang belum mengisi pada scope ini." /></td></tr>
                        @endif
                    @else
                        @forelse($responses as $response)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $response->student?->name }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $response->chosenStudent?->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ \App\Models\SociometryResponse::TYPES[$response->relation_type] ?? $response->relation_type }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $response->reason ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8"><x-empty-state title="Belum ada relasi" description="Data akan muncul setelah siswa mengisi sosiometri." /></td></tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
