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
            <span class="app-sidebar__logo">BK</span>
            <span class="min-w-0">
                <span class="app-sidebar__brand-title">BK System</span>
                <span class="app-sidebar__brand-subtitle">{{ $roleLabel }}</span>
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
        @foreach($menuGroups as $group)
            @php
                $section = $group['section'] ?? 'main';
            @endphp

            @if($section === 'other')
                <div class="app-sidebar__divider" aria-hidden="true"></div>
            @endif

            <div class="app-sidebar__group">
                <p class="app-sidebar__group-label">{{ $group['group'] }}</p>
                <ul class="space-y-0.5">
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
                                @class(['app-sidebar__link', 'app-sidebar__link--active' => $active])
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
        <div class="flex items-center gap-3">
            <span class="app-sidebar__avatar">{{ $initials ?: 'U' }}</span>
            <div class="min-w-0 flex-1">
                <p class="app-sidebar__user-name">{{ $user?->name }}</p>
                <p class="app-sidebar__user-meta">{{ $userIdentity }}</p>
            </div>
        </div>
        <div class="mt-2 flex gap-2">
            @if($hasProfile)
                <a
                    href="{{ route('profile.edit') }}"
                    @class(['app-sidebar__action w-full', 'app-sidebar__action--active' => request()->routeIs('profile.*')])
                    x-on:click="$dispatch('close-sidebar')"
                >
                    Profil
                </a>
            @endif
        </div>
    </div>
</aside>
