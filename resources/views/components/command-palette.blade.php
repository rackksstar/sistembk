@props(['items' => []])

{{--
    Palet perintah (command palette) untuk navigasi cepat.

    Semua menu peran aktif dikumpulkan menjadi satu daftar. Pengguna bisa
    mengetik sebagian nama menu lalu menekan Enter untuk langsung membuka.
    Dipicu tombol di topbar atau pintasan Ctrl+K / Cmd+K.
--}}

<div
    x-data="{
        open: false,
        query: '',
        cursor: 0,
        items: @js($items),
        get results() {
            const q = this.query.trim().toLowerCase();
            if (q === '') {
                return this.items.slice(0, 10);
            }
            return this.items
                .filter((item) => `${item.label} ${item.group}`.toLowerCase().includes(q))
                .slice(0, 20);
        },
        show() {
            this.open = true;
            this.query = '';
            this.cursor = 0;
            this.$nextTick(() => this.$refs.input.focus());
        },
        hide() {
            this.open = false;
        },
        move(delta) {
            const total = this.results.length;
            if (total === 0) return;
            this.cursor = (this.cursor + delta + total) % total;
        },
        go() {
            const item = this.results[this.cursor];
            if (!item) return;
            this.hide();
            window.location.href = item.href;
        },
    }"
    x-on:keydown.window="if (($event.ctrlKey || $event.metaKey) && ($event.key === 'k' || $event.key === 'K')) { $event.preventDefault(); open ? hide() : show(); }"
    x-on:keydown.escape.window="if (open) { $event.preventDefault(); hide(); }"
>
    <button
        type="button"
        class="app-topbar__search-btn"
        x-on:click="show()"
        title="Cari menu (Ctrl+K)"
        aria-label="Cari menu (Ctrl+K)"
    >
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
        <span class="app-topbar__search-btn-text">Cari menu...</span>
        <kbd class="app-topbar__search-kbd">Ctrl K</kbd>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[110] flex items-start justify-center bg-slate-950/60 px-4 pt-[10vh] backdrop-blur-xs dark:bg-slate-950/80"
        role="dialog"
        aria-modal="true"
        aria-label="Cari menu navigasi"
        x-on:click.outside="hide()"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/20 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/40"
            x-on:keydown.down.prevent="move(1)"
            x-on:keydown.up.prevent="move(-1)"
            x-on:keydown.enter.prevent="go()"
        >
            <div class="flex items-center gap-3 border-b border-slate-200 px-4 dark:border-slate-800">
                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input
                    x-ref="input"
                    type="text"
                    x-model="query"
                    x-on:input="cursor = 0"
                    placeholder="Ketik nama menu, lalu Enter untuk membuka..."
                    class="w-full border-0 bg-transparent py-4 text-sm text-slate-900 outline-hidden placeholder:text-slate-400 focus:ring-0 dark:text-slate-100 dark:placeholder:text-slate-500"
                    autocomplete="off"
                    aria-label="Kata kunci pencarian menu"
                >
                <button
                    type="button"
                    class="rounded-lg px-2 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    x-on:click="hide()"
                >Esc</button>
            </div>

            <div class="max-h-[22rem] overflow-y-auto p-2">
                <template x-if="results.length === 0">
                    <p class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                        Menu "<span x-text="query"></span>" tidak ditemukan.
                    </p>
                </template>

                <template x-for="(item, index) in results" :key="item.href + index">
                    <a
                        :href="item.href"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition"
                        :class="index === cursor ? 'bg-blue-50 text-blue-800 dark:bg-blue-950/50 dark:text-blue-200' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800'"
                        x-on:mouseenter="cursor = index"
                        x-on:click="hide()"
                    >
                        <span class="min-w-0 flex-1 truncate" x-text="item.label"></span>
                        <span class="shrink-0 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400" x-text="item.group"></span>
                    </a>
                </template>
            </div>

            <div class="flex items-center gap-4 border-t border-slate-200 bg-slate-50 px-4 py-2 text-[10px] font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-400">
                <span>↑ ↓ untuk memilih</span>
                <span>Enter untuk membuka</span>
                <span>Esc untuk menutup</span>
            </div>
        </div>
    </div>
</div>
