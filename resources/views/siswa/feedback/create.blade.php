@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <x-section-title title="Feedback Layanan BK" description="Kirim penilaian dan masukan setelah menerima layanan." />
        <x-alert class="mt-5" type="success" :message="session('success')" />
        @if($errors->any())
            <x-alert class="mt-5" type="error" message="Periksa kembali feedback Anda." />
        @endif
    </section>

    <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <form method="POST" action="{{ route('siswa.feedback.store') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="space-y-4">
                <div>
                    <x-input-label value="Layanan Terkait" />
                    <select name="consultation_request_id" class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        <option value="">Umum / tidak terkait sesi tertentu</option>
                        @foreach($consultations as $consultation)
                            <option value="{{ $consultation->id }}">{{ $consultation->subject }}</option>
                        @endforeach
                    </select>
                </div>
                <x-text-input name="service_type" required value="{{ old('service_type', 'Konseling Individu') }}" class="w-full" placeholder="Jenis layanan" />
                <x-text-input name="rating" type="number" min="1" max="5" required value="{{ old('rating', 5) }}" class="w-full" placeholder="Rating 1-5" />
                <textarea name="message" rows="5" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Apa yang Anda rasakan dari layanan ini?">{{ old('message') }}</textarea>
                <textarea name="suggestion" rows="4" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" placeholder="Saran untuk layanan BK">{{ old('suggestion') }}</textarea>
                <x-primary-button>Kirim Feedback</x-primary-button>
            </div>
        </form>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-section-title title="Riwayat Feedback" description="Feedback yang sudah Anda kirim." />
            <div class="mt-5 space-y-3">
                @forelse($feedback as $item)
                    <article class="rounded-2xl bg-slate-50 p-4 text-sm">
                        <p class="font-semibold text-slate-950">{{ $item->service_type }} - {{ $item->rating }}/5</p>
                        <p class="mt-1 text-slate-500">{{ $item->consultation?->subject ?? 'Feedback umum' }}</p>
                        <p class="mt-2 text-slate-600">{{ $item->message }}</p>
                    </article>
                @empty
                    <x-empty-state title="Belum ada feedback" description="Feedback Anda akan tampil di sini." />
                @endforelse
            </div>
        </aside>
    </section>
</div>
@endsection
