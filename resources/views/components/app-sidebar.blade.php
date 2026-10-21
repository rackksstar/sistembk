@props([
    'menuGroups' => [],
    'dashboardRoute' => '#',
    'roleLabel' => 'Pengguna',
    'user' => null,
    'userIdentity' => '-',
])

@php
    use App\Support\NavigationIcons;

    $initials = collect(explode(' ', (string) ($user?->name ?? 'U')))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');

    $hasProfile = $user && in_array($user->role, ['admin', 'guru'], true);

    $isItemActive = function (array $item): bool {
        $patterns = (array) ($item['active'] ?? $item['route'] ?? '');
        $routeMatches = collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern));

        if (! $routeMatches) {
            return false;
        }

        // Cocokkan query params (category / module) agar index Yola vs Key tidak saling aktif.
        foreach ((array) ($item['match'] ?? []) as $key => $value) {
            $actual = request()->query($key);

            if ($actual === null || $actual === '') {
                if ($key === 'category' && request()->routeIs('siswa.instruments.*')) {
                    // Infer kategori dari halaman hasil / default index (minat_kerja).
                    if (request()->routeIs('siswa.instruments.strategi-belajar-result')) {
                        $actual = 'gaya_belajar';
                    } elseif (request()->routeIs(['siswa.instruments.hasil', 'siswa.instruments.hasil.pdf'])) {
                        $submission = request()->route('submission');
                        $actual = is_object($submission) ? (string) ($submission->category ?? '') : '';
                    } else {
                        $actual = 'minat_kerja';
                    }
                } elseif ($key === 'module' && request()->routeIs(['guru.instrument-questions.*', 'guru.instrument-results.*'])) {
                    $actual = 'yola';
                } else {
                    return false;
                }
            }

            if ($actual === '' || (string) $actual !== (string) $value) {
                return false;
            }
        }

        // Item Key yang memantau hasil RIASEC: jangan aktif di hasil instrumen Yola.
        if (
            request()->routeIs(['siswa.instruments.hasil', 'siswa.instruments.hasil.pdf'])
            && collect($patterns)->contains(fn ($pattern) => str_contains((string) $pattern, 'instruments.hasil'))
        ) {
            $submission = request()->route('submission');
            $category = is_object($submission) ? (string) ($submission->category ?? '') : '';
            if ($category !== '' && $category !== 'minat_bakat') {
                return false;
            }
        }

        return true;
    };

    $shownDividers = [];

    // Grup yang terbuka secara default: grup aktif + section yang selalu
    // terbuka (main, core, yola, key, platform) agar fitur tidak "hilang".
    $defaultOpenGroups = [];
    $activeGroupIndex = null;
    foreach ($menuGroups as $idx => $group) {
        $section = $group['section'] ?? 'main';
        $groupActive = collect($group['items'] ?? [])->contains(fn ($item) => $isItemActive($item));

        if ($groupActive && $activeGroupIndex === null) {
            $activeGroupIndex = $idx;
        }

        if ($groupActive || in_array($section, ['main', 'core', 'yola', 'key', 'platform'], true)) {
            $defaultOpenGroups[] = $idx;
        }
    }
@endphp

<aside
    {{ $attributes->merge(['class' => 'app-sidebar']) }}
    aria-label="Menu navigasi"
    x-data="{
        search: '',
        openGroups: @js($defaultOpenGroups),
        init() {
            // Pilihan buka/tutup milik pengguna disimpan agar tidak terulang
            // setiap kali halaman dimuat. Grup halaman yang sedang aktif tetap
            // dibuka supaya tidak pernah tersembunyi.
            const bawaan = @js($defaultOpenGroups);
            const grupAktif = @js($activeGroupIndex);

            try {
                const simpan = localStorage.getItem('sidebar:openGroups:{{ $user?->id ?? 'anon' }}');
                if (simpan) {
                    const parsed = JSON.parse(simpan);
                    if (Array.isArray(parsed)) {
                        this.openGroups = grupAktif === null
                            ? [...new Set(parsed)]
                            : [...new Set([...parsed, grupAktif])];
                    }
                }
            } catch (e) {
                // localStorage tidak tersedia (mode privat) — pakai bawaan.
                this.openGroups = bawaan;
            }
            this.$nextTick(() => {
                const aktifLink = this.$el.querySelector('.app-sidebar__link--active');
                if (aktifLink) {
                    setTimeout(() => aktifLink.scrollIntoView({ block: 'nearest' }), 120);
                }
            });
        },
        toggleGroup(idx) {
            this.openGroups = this.openGroups.includes(idx)
                ? this.openGroups.filter((i) => i !== idx)
                : [...this.openGroups, idx];
            try {
                localStorage.setItem('sidebar:openGroups:{{ $user?->id ?? 'anon' }}', JSON.stringify(this.openGroups));
            } catch (e) {}
        },
        isOpen(idx) {
            if (this.search !== '') return true;
            return this.openGroups.includes(idx);
        },
        clearSearch() {
            this.search = '';
        },
        grupAda(label) {
            if (this.search === '') return true;
            return String(label).toLowerCase().includes(this.search.toLowerCase());
        },
        itemAda(label) {
            if (this.search === '') return true;
            return String(label).toLowerCase().includes(this.search.toLowerCase());
        }
    }"
    @keydown.escape="clearSearch()"
>
    <div class="app-sidebar__header">
        <a href="{{ $dashboardRoute }}" class="app-sidebar__brand" x-on:click="$dispatch('close-sidebar')" title="Kembali ke Dashboard">
            <span class="app-sidebar__logo" aria-hidden="true">
                <span>BK</span>
            </span>
            <span class="min-w-0">
                <span class="app-sidebar__brand-title">BK System</span>
                <span class="app-sidebar__role-pill">{{ $roleLabel }}</span>
            </span>
        </a>
        <button
            type="button"
            class="app-sidebar__close lg:hidden"
            x-on:click="$dispatch('close-sidebar')"
            aria-label="Tutup menu"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div class="app-sidebar__search">
        <div class="app-sidebar__search-wrap">
            <svg class="app-sidebar__search-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
            </svg>
            <input
                type="search"
                x-model.debounce.200ms="search"
                placeholder="Cari menu..."
                class="app-sidebar__search-input"
                autocomplete="off"
                aria-label="Cari menu navigasi"
            >
            <button
                x-show="search !== ''"
                x-on:click="clearSearch()"
                class="app-sidebar__search-clear"
                type="button"
                aria-label="Bersihkan pencarian"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                </svg>
            </button>
        </div>
    </div>

    <nav class="app-sidebar__nav" aria-label="Menu utama">
        @foreach($menuGroups as $groupIndex => $group)
            @php
                $section = $group['section'] ?? 'main';
                $groupActive = collect($group['items'] ?? [])->contains(fn ($item) => $isItemActive($item));
                // Utama, core, yola, key, platform selalu terbuka agar fitur tidak "hilang".
                $defaultOpen = $groupActive || in_array($section, ['main', 'core', 'yola', 'key', 'platform'], true);
                $groupIcon = $group['icon'] ?? match ($section) {
                    'core' => 'clipboard',
                    'yola' => 'beaker',
                    'key' => 'academic',
                    'other' => 'puzzle',
                    'platform' => 'grid',
                    default => 'home',
                };
                $isYola = $section === 'yola';
                $isKey = $section === 'key';
                $isOther = $section === 'other';
                $dividerLabel = null;
                if ($isYola || $isOther) {
                    if (! in_array('Tim lain', $shownDividers, true)) {
                        $shownDividers[] = 'Tim lain';
                        $dividerLabel = 'Tim lain';
                    }
                } elseif ($isKey && ! in_array('Modul Key', $shownDividers, true)) {
                    $shownDividers[] = 'Modul Key';
                    $dividerLabel = 'Modul Key';
                }
            @endphp

            @if($dividerLabel)
                <div class="app-sidebar__divider" role="separator">
                    <span>{{ $dividerLabel }}</span>
                </div>
            @endif

            <div
                class="app-sidebar__group"
                x-show="grupAda(@js($group['group'] ?? '')) || {{ Js::from(collect($group['items'] ?? [])->pluck('label')->all()) }}.some(l => String(l).toLowerCase().includes(search.toLowerCase()))"
            >
                <button
                    type="button"
                    class="app-sidebar__group-toggle"
                    x-on:click="toggleGroup({{ $groupIndex }})"
                    :aria-expanded="isOpen({{ $groupIndex }}).toString()"
                    :class="{ 'app-sidebar__group-toggle--active': {{ $groupActive ? 'true' : 'false' }} && search === '' }"
                    aria-controls="sidebar-group-{{ $groupIndex }}"
                >
                    <span class="flex min-w-0 items-center gap-2.5">
                        <span @class([
                            'app-sidebar__group-icon',
                            'app-sidebar__group-icon--active' => $groupActive,
                            'app-sidebar__group-icon--muted' => $isOther && ! $groupActive,
                        ])>
                            <x-nav-icon :name="$groupIcon" class="h-[1.05rem] w-[1.05rem] shrink-0" />
                        </span>
                        <span class="truncate flex-1">{{ $group['group'] }}</span>
                        @if(count($group['items'] ?? []) > 1)
                            <span
                                class="app-sidebar__count"
                                :class="{ 'app-sidebar__count--active': {{ $groupActive ? 'true' : 'false' }} && search === '' }"
                            >{{ count($group['items']) }}</span>
                        @endif
                    </span>
                    <svg
                        class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200 ease-in-out dark:text-slate-500"
                        :class="{ 'rotate-180 text-blue-600 dark:text-blue-400': isOpen({{ $groupIndex }}) }"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>

                <ul
                    id="sidebar-group-{{ $groupIndex }}"
                    @if(! $defaultOpen) x-cloak @endif
                    x-show="isOpen({{ $groupIndex }})"
                    x-collapse.duration.200ms
                    class="app-sidebar__list"
                >
                    @foreach($group['items'] as $item)
                        @php
                            $active = $isItemActive($item);
                            $href = isset($item['route'])
                                ? route($item['route'], $item['params'] ?? [])
                                : $dashboardRoute;
                            $icon = $item['icon'] ?? NavigationIcons::forRoute($item['route'] ?? '');
                            $label = $item['label'] ?? '';
                        @endphp
                        <li x-show="itemAda(@js($label))">
                            <a
                                href="{{ $href }}"
                                x-on:click="$dispatch('close-sidebar')"
                                @class([
                                    'app-sidebar__link',
                                    'app-sidebar__link--active' => $active,
                                    // Jangan mute modul Key — harus terlihat sama interaktifnya dengan menu lain.
                                    'app-sidebar__link--muted' => $isOther && ! $active,
                                ])
                                @if($active) aria-current="page" @endif
                            >
                                <x-nav-icon :name="$icon" class="h-[1.125rem] w-[1.125rem] shrink-0" />
                                <span class="truncate flex-1">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div
            x-show="search !== '' && @js(collect($menuGroups)->flatMap(fn ($g) => collect($g['items'] ?? [])->pluck('label'))->all()).filter(l => String(l).toLowerCase().includes(search.toLowerCase())).length === 0"
            class="px-4 py-8 text-center"
        >
            <svg class="mx-auto h-8 w-8 text-slate-400 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <p class="mt-3 text-xs font-semibold text-slate-600 dark:text-slate-400">Menu tidak ditemukan</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-500">Coba kata kunci lain</p>
        </div>
    </nav>

    <div class="app-sidebar__footer">
        <div class="app-sidebar__user">
            <span class="app-sidebar__avatar" aria-hidden="true">{{ $initials ?: 'U' }}</span>
            <div class="min-w-0 flex-1">
                <p class="app-sidebar__user-name">{{ $user?->name }}</p>
                <p class="app-sidebar__user-meta">{{ $userIdentity }}</p>
            </div>
        </div>
        <div class="app-sidebar__actions">
            @if($hasProfile)
                <a
                    href="{{ route('profile.edit') }}"
                    @class(['app-sidebar__action', 'app-sidebar__action--active' => request()->routeIs('profile.*')])
                    x-on:click="$dispatch('close-sidebar')"
                >
                    <x-nav-icon name="user-circle" class="h-3.5 w-3.5" />
                    Profil
                </a>
            @endif
            <button
                type="button"
                class="app-sidebar__action app-sidebar__action--logout"
                x-on:click="$dispatch('open-logout')"
            >
                Keluar
            </button>
        </div>
    </div>
</aside>
