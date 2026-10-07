<form method="POST" action="{{ route('siswa.instruments.store') }}" class="space-y-5">
    @csrf
    <input type="hidden" name="category" value="{{ $category }}">

    @foreach($questions as $question)
        <div class="rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-5">
            <p class="font-semibold leading-7 text-slate-950 dark:text-white">{{ $loop->iteration }}. {{ $question->question }}</p>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach($question->options as $index => $option)
                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-white dark:border-slate-700 bg-white dark:bg-slate-900 p-4 text-sm font-medium text-slate-700 dark:text-slate-300 shadow-sm transition hover:border-blue-200 dark:hover:border-blue-800">
                        <input
                            type="radio"
                            name="answers[{{ $question->id }}]"
                            value="{{ $index }}"
                            required
                            @checked((string) old('answers.'.$question->id) === (string) $index)
                            class="border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500"
                        >
                        {{ $option['label'] }}
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    @error('answers')
        <x-alert type="error" :message="$message" />
    @enderror

    <x-primary-button class="rounded-full px-6 py-3">Kirim dan Lihat Skor</x-primary-button>
</form>
