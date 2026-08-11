@extends('layouts.app')

@section('content')
<div
    class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]"
    x-data="{
        message: '',
        sending: false,
        messages: [
            { role: 'assistant', text: 'Halo, saya siap mendengarkan. Ceritakan apa yang sedang kamu rasakan atau butuhkan hari ini.' },
        ],
        async send() {
            const text = this.message.trim();

            if (! text || this.sending) {
                return;
            }

            this.messages.push({ role: 'user', text });
            this.message = '';
            this.sending = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const response = await fetch('{{ route('siswa.chatbot.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ message: text }),
                });
                const data = await response.json();

                this.messages.push({
                    role: 'assistant',
                    text: data.reply || 'Maaf, belum ada balasan yang bisa ditampilkan.',
                });
            } catch (error) {
                this.messages.push({
                    role: 'assistant',
                    text: 'Maaf, koneksi chatbot sedang bermasalah. Coba kirim ulang sebentar lagi.',
                });
            } finally {
                this.sending = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        scrollToBottom() {
            if (this.$refs.chatBody) {
                this.$refs.chatBody.scrollTop = this.$refs.chatBody.scrollHeight;
            }
        },
    }"
>
    <section class="ui-panel flex min-h-[72vh] flex-col p-0">
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 p-5 dark:border-slate-700">
            <div class="flex min-w-0 items-center gap-3">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-sm shadow-blue-500/30">
                    <x-nav-icon name="chat" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <h1 class="truncate text-lg font-bold text-slate-950 dark:text-slate-100">Chatbot Konseling</h1>
                    <p class="truncate text-sm text-slate-500 dark:text-slate-400">BK System</p>
                </div>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Online</span>
        </div>

        <div x-ref="chatBody" class="min-h-0 flex-1 space-y-4 overflow-y-auto bg-slate-50 p-5 dark:bg-slate-950/40">
            <template x-for="(item, index) in messages" :key="index">
                <div class="flex" :class="item.role === 'user' ? 'justify-end' : 'justify-start'">
                    <div
                        class="max-w-[82%] rounded-3xl px-4 py-3 text-sm leading-6 shadow-sm"
                        :class="item.role === 'user'
                            ? 'rounded-br-lg bg-blue-600 text-white shadow-blue-500/20'
                            : 'rounded-bl-lg border border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200'"
                    >
                        <p class="whitespace-pre-line" x-text="item.text"></p>
                    </div>
                </div>
            </template>

            <div x-show="sending" x-cloak class="flex justify-start">
                <div class="rounded-3xl rounded-bl-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                    Mengetik...
                </div>
            </div>
        </div>

        <form x-on:submit.prevent="send" class="border-t border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <div class="flex gap-3">
                <input
                    x-model="message"
                    type="text"
                    class="ui-input"
                    placeholder="Tulis pesan..."
                    maxlength="2000"
                    autocomplete="off"
                >
                <button
                    type="submit"
                    class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                    x-bind:disabled="sending || !message.trim()"
                    aria-label="Kirim pesan"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3.48 20.1 21.2 12 3.48 3.9 3 10.2l10.2 1.8L3 13.8l.48 6.3Z" />
                    </svg>
                </button>
            </div>
        </form>
    </section>

    <aside class="space-y-4">
        <section class="ui-panel">
            <x-section-title
                title="Siswa"
                description="Percakapan dikirim dengan identitas siswa aktif."
            />
            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-800/70">
                <p class="font-semibold text-slate-950 dark:text-slate-100">{{ auth()->user()->name }}</p>
                <p class="mt-1 text-slate-500 dark:text-slate-400">ID: {{ $studentId }}</p>
            </div>
        </section>

        <section class="ui-panel">
            <x-section-title
                title="Layanan BK"
                description="Untuk kondisi darurat, hubungi Guru BK atau pihak sekolah secara langsung."
            />
            <a href="{{ route('siswa.consultations.index') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">
                Ajukan Konseling
            </a>
        </section>
    </aside>
</div>
@endsection
