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

        return collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern));
    };
@endphp

<aside
    {{ $attributes->merge(['class' => 'app-sidebar']) }}
    aria-label="Menu navigasi"
>
    <div class="app-sidebar__header">
        <a href="{{ $dashboardRoute }}" class="app-sidebar__brand" x-on:click="$dispatch('close-sidebar')">
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

    <nav class="app-sidebar__nav" aria-label="Menu utama">
        @foreach($menuGroups as $groupIndex => $group)
            @php
                $section = $group['section'] ?? 'main';
                $groupActive = collect($group['items'] ?? [])->contains(fn ($item) => $isItemActive($item));
                // Utama + core selalu terbuka; modul tim lain hanya jika aktif.
                $defaultOpen = $groupActive || in_array($section, ['main', 'core', 'platform'], true);
                $groupIcon = $group['icon'] ?? match ($section) {
                    'core' => 'clipboard',
                    'other' => 'puzzle',
                    'platform' => 'grid',
                    default => 'home',
                };
                $isOther = $section === 'other';
            @endphp

            @if($isOther)
                <div class="app-sidebar__divider" role="separator">
                    <span>Tim lain</span>
                </div>
            @endif

            <div
                class="app-sidebar__group"
                x-data="{ open: {{ $defaultOpen ? 'true' : 'false' }} }"
            >
                <button
                    type="button"
                    class="app-sidebar__group-toggle"
                    x-on:click="open = !open"
                    :aria-expanded="open.toString()"
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
                        <span class="truncate">{{ $group['group'] }}</span>
                        @if(count($group['items'] ?? []) > 1)
                            <span class="app-sidebar__count">{{ count($group['items']) }}</span>
                        @endif
                    </span>
                    <svg
                        class="h-4 w-4 shrink-0 text-slate-400 transition duration-200 dark:text-slate-500"
                        :class="{ 'rotate-180 text-blue-600 dark:text-blue-400': open }"
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
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="app-sidebar__list"
                >
                    @foreach($group['items'] as $item)
                        @php
                            $active = $isItemActive($item);
                            $href = isset($item['route']) ? route($item['route']) : $dashboardRoute;
                            $icon = $item['icon'] ?? NavigationIcons::forRoute($item['route'] ?? '');
                        @endphp
                        <li>
                            <a
                                href="{{ $href }}"
                                x-on:click="$dispatch('close-sidebar')"
                                @class([
                                    'app-sidebar__link',
                                    'app-sidebar__link--active' => $active,
                                    'app-sidebar__link--muted' => $isOther && ! $active,
                                ])
                                @if($active) aria-current="page" @endif
                            >
                                <x-nav-icon :name="$icon" class="h-[1.125rem] w-[1.125rem] shrink-0" />
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
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
