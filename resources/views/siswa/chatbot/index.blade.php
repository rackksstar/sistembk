@extends('layouts.app')

@section('content')
@php
    $firstName = explode(' ', trim((string) auth()->user()->name))[0] ?: 'Siswa';
    $chatConfig = [
        'endpoint' => route('siswa.chatbot.store'),
        'csrf' => csrf_token(),
        'floating' => false,
        'messages' => [
            [
                'role' => 'assistant',
                'text' => "Halo, {$firstName}! Aku siap mendampingi kamu.\n\nBoleh cerita soal belajar atau konseling, atau tanya info resmi PCR/PMB (jalur masuk, prodi, biaya, beasiswa). Mau mulai dari mana?",
            ],
        ],
        'quickMessages' => [
            'Jalur masuk PCR apa saja?',
            'Prodi apa saja di PCR?',
            'Berapa biaya kuliah PCR?',
            'IPS bisa daftar prodi apa di PSUD?',
            'Apa itu Minat Bakat Kuliah?',
        ],
    ];
@endphp

<div
    class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]"
    x-data="siswaChatbot(@js($chatConfig))"
>
    <section class="ui-panel flex min-h-[72vh] flex-col overflow-hidden p-0">
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 dark:border-slate-700">
            <div class="flex min-w-0 items-center gap-3">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-xs shadow-blue-500/30">
                    <x-nav-icon name="chat" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <h1 class="truncate text-lg font-bold text-slate-950 dark:text-slate-100">Chatbot Konseling</h1>
                    <p class="flex items-center gap-2 truncate text-sm text-slate-500 dark:text-slate-400">
                        <span class="chat-status-dot" aria-hidden="true"></span>
                        <span x-text="busy ? (sending ? 'Sedang membalas…' : 'Menulis balasan…') : 'Online · Teman BK'"></span>
                    </p>
                </div>
            </div>
            <span class="hidden rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 sm:inline-flex dark:bg-emerald-950/40 dark:text-emerald-300">Siap membantu</span>
        </div>

        <div x-ref="chatBody" class="min-h-0 flex-1 space-y-4 overflow-y-auto scroll-smooth bg-slate-50 p-5 dark:bg-slate-950/40">
            <template x-for="item in messages" :key="item.id">
                <div class="chat-bubble-in flex" :class="item.role === 'user' ? 'justify-end' : 'justify-start'">
                    <div
                        class="max-w-[82%] rounded-3xl px-4 py-3 text-sm leading-6 shadow-xs"
                        :class="item.role === 'user'
                            ? 'rounded-br-lg bg-blue-600 text-white shadow-blue-500/20'
                            : 'rounded-bl-lg border border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200'"
                    >
                        <div class="chat-md break-words" x-html="formatChatHtml(item.text)"></div>
                        <span x-show="item.streaming" x-cloak class="chat-caret" aria-hidden="true"></span>
                    </div>
                </div>
            </template>

            <div x-show="sending" x-cloak class="chat-bubble-in flex justify-start" role="status" aria-live="polite">
                <div class="inline-flex items-center gap-1.5 rounded-3xl rounded-bl-lg border border-slate-200 bg-white px-4 py-3 shadow-xs dark:border-slate-700 dark:bg-slate-900">
                    <span class="chat-typing-dot"></span>
                    <span class="chat-typing-dot"></span>
                    <span class="chat-typing-dot"></span>
                    <span class="ml-2 text-xs font-medium text-slate-500 dark:text-slate-400">mengetik</span>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-3 flex gap-2 overflow-x-auto pb-1">
                <template x-for="hint in quickMessages" :key="hint">
                    <button
                        type="button"
                        class="shrink-0 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-blue-300 hover:text-blue-700 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:text-blue-300"
                        x-bind:disabled="busy"
                        x-on:click="useQuickMessage(hint)"
                        x-text="hint"
                    ></button>
                </template>
            </div>

            <form x-on:submit.prevent="send()" class="flex gap-3">
                <input
                    x-ref="chatInput"
                    x-model="message"
                    type="text"
                    class="ui-input"
                    placeholder="Tulis pesan… misalnya: jalur masuk PCR"
                    maxlength="2000"
                    autocomplete="off"
                    x-bind:disabled="busy"
                >
                <button
                    type="submit"
                    class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                    x-bind:disabled="busy || !message.trim()"
                    aria-label="Kirim pesan"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M3.48 20.1 21.2 12 3.48 3.9 3 10.2l10.2 1.8L3 13.8l.48 6.3Z" />
                    </svg>
                </button>
            </form>
        </div>
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
            @if(\Illuminate\Support\Facades\Route::has('siswa.konsultasi-prodi.index'))
                <a href="{{ route('siswa.konsultasi-prodi.index') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-300">
                    Konsultasi Prodi PCR
                </a>
            @endif
            @if(\Illuminate\Support\Facades\Route::has('siswa.minat-bakat.index'))
                <a href="{{ route('siswa.minat-bakat.index') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    Minat Bakat Kuliah
                </a>
            @endif
        </section>

        <section class="ui-panel">
            <x-section-title
                title="Tips ngobrol"
                description="Jawaban PCR mengacu situs resmi pmb.pcr.ac.id / pcr.ac.id."
            />
            <ul class="mt-4 space-y-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                <li>• Tulis pertanyaan spesifik agar jawaban lebih tepat.</li>
                <li>• Follow-up boleh singkat, misalnya “berapa biayanya?”.</li>
                <li>• Masalah darurat: langsung ke Guru BK / orang dewasa terpercaya.</li>
            </ul>
        </section>
    </aside>
</div>
@endsection
