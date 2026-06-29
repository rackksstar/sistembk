@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-2xl border border-transparent bg-slate-200 dark:bg-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-800 dark:text-slate-200 transition hover:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 disabled:opacity-50 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600 dark:focus:ring-slate-500 dark:focus:ring-offset-slate-900']) }} @disabled($disabled)>
    {{ $slot }}
</button>
