<nav x-data="{ open: false, logoutOpen: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- LEFT -->
            <div class="flex">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Menu -->
                @auth
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    <x-nav-link :href="route('booking.index')" :active="request()->routeIs('booking.*')">
                        Booking
                    </x-nav-link>

                    <x-nav-link :href="route('riwayat.index')" :active="request()->routeIs('riwayat.*')">
                        Riwayat
                    </x-nav-link>

                </div>
                @endauth

            </div>

            <!-- RIGHT -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                @guest
                <!-- Kalau belum login -->
                <div class="flex space-x-4">
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-500">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="text-gray-600 hover:text-blue-500">
                        Register
                    </a>
                </div>
                @endguest

                @auth
                <!-- Kalau sudah login -->
                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-700 focus:outline-none transition">

                            <div>{{ auth()->user()->name }}</div>

                            <div class="ms-1">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                </svg>
                            </div>

                        </button>
                    </x-slot>

                    <x-slot name="content">

                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>

                            <x-dropdown-link :href="route('logout')"
                                x-on:click.prevent="logoutOpen = true">
                                Logout
                            </x-dropdown-link>

                    </x-slot>

                </x-dropdown>
                @endauth

            </div>

            <!-- HAMBURGER -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- MOBILE -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden">

        @auth
        <div class="pt-2 pb-3 space-y-1">

            <x-responsive-nav-link :href="route('dashboard')">
                Dashboard
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('booking.index')">
                Booking
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('riwayat.index')">
                Riwayat
            </x-responsive-nav-link>

        </div>

        <div class="pt-4 pb-1 border-t">
            <div class="px-4">
                <div class="font-medium text-gray-800">
                    {{ auth()->user()->name }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ auth()->user()->email }}
                </div>
            </div>

            <div class="mt-3 space-y-1">

                <x-responsive-nav-link :href="route('profile.edit')">
                    Profile
                </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('logout')"
                        x-on:click.prevent="logoutOpen = true">
                        Logout
                    </x-responsive-nav-link>

            </div>
        </div>
        @endauth

        @guest
        <div class="p-4 space-y-2">
            <a href="{{ route('login') }}" class="block text-gray-600">Login</a>
            <a href="{{ route('register') }}" class="block text-gray-600">Register</a>
        </div>
        @endguest

    </div>

    @auth
        <div
            x-cloak
            x-show="logoutOpen"
            x-transition.opacity
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="navigation-logout-confirmation-title"
        >
            <div x-on:click.outside="logoutOpen = false" class="w-full max-w-md rounded-2xl border border-white bg-white p-6 text-center shadow-2xl shadow-slate-950/20">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-blue-600">Konfirmasi Logout</p>
                <h2 id="navigation-logout-confirmation-title" class="mt-3 text-2xl font-bold text-slate-950">Yakin ingin keluar?</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Anda masih login sebagai {{ auth()->user()->name }}. Setelah logout, Anda harus login ulang untuk masuk dashboard.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <button type="button" x-on:click="logoutOpen = false" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">
                            Ya, logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endauth

</nav>
