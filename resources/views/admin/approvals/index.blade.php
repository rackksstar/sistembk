@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <x-section-title
                title="Persetujuan Guru BK"
                description="Tinjau pendaftaran Guru BK lalu setujui atau tolak akses dashboard."
            />

            <form method="GET" action="{{ route('admin.approvals.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <select name="status" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <option value="semua" @selected($status === 'semua')>Semua status</option>
                    @foreach(\App\Models\User::STATUSES as $item)
                        <option value="{{ $item }}" @selected($status === $item)>{{ ucfirst($item) }}</option>
                    @endforeach
                </select>
                <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Filter</button>
            </form>
        </div>

        <x-alert class="mt-5" type="success" :message="session('success')" />

        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Nama</th>
                            <th class="px-5 py-4">Email</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($teachers as $teacher)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $teacher->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $teacher->email }}</td>
                                <td class="px-5 py-4"><x-status-badge :status="$teacher->status" /></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.approvals.approve', $teacher) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-500">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.approvals.reject', $teacher) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="rounded-2xl bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-500">Reject</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6">
                                    <x-empty-state title="Tidak ada data guru" description="Data Guru BK dengan filter ini belum tersedia." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $teachers->links() }}
        </div>
    </section>
</div>
@endsection
