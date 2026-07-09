@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-2xl border border-transparent bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-white disabled:opacity-50 dark:focus:ring-offset-slate-900']) }} @disabled($disabled)>
    {{ $slot }}
</button>
