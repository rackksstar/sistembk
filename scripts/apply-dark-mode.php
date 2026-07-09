<?php

/**
 * Terapkan kelas dark: ke semua blade (sekali jalan).
 * Skip: print, pdf, file yang sudah punya dark: lengkap.
 */

$root = dirname(__DIR__);
$viewsPath = $root.'/resources/views';

$skipPatterns = [
    '/print\.blade\.php$/',
    '/pdf\.blade\.php$/',
    '/navigation\.blade\.php$/',
    '/theme-init\.blade\.php$/',
];

$replacements = [
  // Backgrounds
    '/\bbg-white\/90\b(?!\s+dark:)/' => 'bg-white/90 dark:bg-slate-900/90',
    '/\bbg-white\/80\b(?!\s+dark:)/' => 'bg-white/80 dark:bg-slate-900/80',
    '/\bbg-white\/75\b(?!\s+dark:)/' => 'bg-white/75 dark:bg-slate-900/75',
    '/\bbg-white\b(?!\s+dark:|\/)/' => 'bg-white dark:bg-slate-900',
    '/\bbg-slate-50\/80\b(?!\s+dark:)/' => 'bg-slate-50/80 dark:bg-slate-800/50',
    '/\bbg-slate-50\b(?!\s+dark:|\/)/' => 'bg-slate-50 dark:bg-slate-800/60',
    '/\bbg-slate-100\b(?!\s+dark:|\/)/' => 'bg-slate-100 dark:bg-slate-800',
    '/\bbg-slate-200\b(?!\s+dark:|\/)/' => 'bg-slate-200 dark:bg-slate-700',
    '/\bbg-blue-50\b(?!\s+dark:)/' => 'bg-blue-50 dark:bg-blue-950/40',
    '/\bbg-emerald-50\b(?!\s+dark:)/' => 'bg-emerald-50 dark:bg-emerald-950/40',
    '/\bbg-red-50\b(?!\s+dark:)/' => 'bg-red-50 dark:bg-red-950/40',
    '/\bbg-amber-50\b(?!\s+dark:)/' => 'bg-amber-50 dark:bg-amber-950/40',
    '/\bbg-rose-50\b(?!\s+dark:)/' => 'bg-rose-50 dark:bg-rose-950/40',
    '/\bbg-gray-50\b(?!\s+dark:)/' => 'bg-gray-50 dark:bg-gray-900',
    '/\bbg-gray-100\b(?!\s+dark:)/' => 'bg-gray-100 dark:bg-gray-800',

  // Borders
    '/\bborder-white\/80\b(?!\s+dark:)/' => 'border-white/80 dark:border-slate-700/80',
    '/\bborder-white\b(?!\s+dark:|\/)/' => 'border-white dark:border-slate-700',
    '/\bborder-slate-100\b(?!\s+dark:)/' => 'border-slate-100 dark:border-slate-800',
    '/\bborder-slate-200\/80\b(?!\s+dark:)/' => 'border-slate-200/80 dark:border-slate-700/80',
    '/\bborder-slate-200\b(?!\s+dark:|\/)/' => 'border-slate-200 dark:border-slate-700',
    '/\bborder-slate-300\b(?!\s+dark:)/' => 'border-slate-300 dark:border-slate-600',
    '/\bborder-blue-100\b(?!\s+dark:)/' => 'border-blue-100 dark:border-blue-900/50',
    '/\bborder-blue-200\b(?!\s+dark:)/' => 'border-blue-200 dark:border-blue-800',
    '/\bborder-emerald-200\b(?!\s+dark:)/' => 'border-emerald-200 dark:border-emerald-800',
    '/\bborder-red-200\b(?!\s+dark:)/' => 'border-red-200 dark:border-red-800',
    '/\bborder-amber-200\b(?!\s+dark:)/' => 'border-amber-200 dark:border-amber-800',
    '/\bborder-dashed\b(?!\s+border-slate-300\s+dark:)/' => 'border-dashed', // noop anchor

  // Text
    '/\btext-slate-950\b(?!\s+dark:)/' => 'text-slate-950 dark:text-white',
    '/\btext-slate-900\b(?!\s+dark:)/' => 'text-slate-900 dark:text-slate-100',
    '/\btext-slate-800\b(?!\s+dark:)/' => 'text-slate-800 dark:text-slate-200',
    '/\btext-slate-700\b(?!\s+dark:)/' => 'text-slate-700 dark:text-slate-300',
    '/\btext-slate-600\b(?!\s+dark:)/' => 'text-slate-600 dark:text-slate-400',
    '/\btext-slate-500\b(?!\s+dark:)/' => 'text-slate-500 dark:text-slate-400',
    '/\btext-gray-700\b(?!\s+dark:)/' => 'text-gray-700 dark:text-gray-300',
    '/\btext-gray-500\b(?!\s+dark:)/' => 'text-gray-500 dark:text-gray-400',
    '/\btext-emerald-700\b(?!\s+dark:)/' => 'text-emerald-700 dark:text-emerald-300',
    '/\btext-red-700\b(?!\s+dark:)/' => 'text-red-700 dark:text-red-300',
    '/\btext-amber-800\b(?!\s+dark:)/' => 'text-amber-800 dark:text-amber-300',
    '/\btext-blue-700\b(?!\s+dark:)/' => 'text-blue-700 dark:text-blue-300',

  // Hover / divide / ring
    '/\bhover:bg-slate-50\b(?!\s+dark:)/' => 'hover:bg-slate-50 dark:hover:bg-slate-800',
    '/\bhover:bg-slate-100\b(?!\s+dark:)/' => 'hover:bg-slate-100 dark:hover:bg-slate-700',
    '/\bhover:bg-slate-200\b(?!\s+dark:)/' => 'hover:bg-slate-200 dark:hover:bg-slate-600',
    '/\bhover:bg-white\b(?!\s+dark:)/' => 'hover:bg-white dark:hover:bg-slate-800',
    '/\bhover:text-slate-900\b(?!\s+dark:)/' => 'hover:text-slate-900 dark:hover:text-white',
    '/\bhover:text-slate-700\b(?!\s+dark:)/' => 'hover:text-slate-700 dark:hover:text-slate-200',
    '/\bhover:text-gray-700\b(?!\s+dark:)/' => 'hover:text-gray-700 dark:hover:text-gray-200',
    '/\bdivide-slate-100\b(?!\s+dark:)/' => 'divide-slate-100 dark:divide-slate-800',
    '/\bdivide-slate-200\b(?!\s+dark:)/' => 'divide-slate-200 dark:divide-slate-700',
    '/\bring-slate-200\b(?!\s+dark:)/' => 'ring-slate-200 dark:ring-slate-700',
    '/\bring-1 ring-slate-200\b(?!\s+dark:)/' => 'ring-1 ring-slate-200 dark:ring-slate-700',

  // Focus rings for inputs
    '/\bfocus:ring-blue-100\b(?!\s+dark:)/' => 'focus:ring-blue-100 dark:focus:ring-blue-900/50',

  // Body / page backgrounds
    '/\bbg-\[#f4f7fc\]\b/' => 'bg-slate-100 dark:bg-slate-950',
    '/\bbg-\[#f5f8ff\]\b/' => 'bg-slate-100 dark:bg-slate-950',
];

$gradientReplacements = [
    'bg-[linear-gradient(135deg,#f8fbff_0%,#edf5ff_52%,#dbe7f5_100%)]' => 'bg-[linear-gradient(135deg,#f8fbff_0%,#edf5ff_52%,#dbe7f5_100%)] dark:bg-gradient-to-br dark:from-slate-950 dark:via-slate-900 dark:to-slate-950',
    'bg-[linear-gradient(135deg,#ffffff_0%,#eef5ff_48%,#b7d3ff_100%)]' => 'bg-[linear-gradient(135deg,#ffffff_0%,#eef5ff_48%,#b7d3ff_100%)] dark:bg-gradient-to-br dark:from-slate-900 dark:via-slate-900 dark:to-slate-800',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath)
);

$updated = 0;
$skipped = 0;

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $relative = str_replace($root.'/', '', $path);

    foreach ($skipPatterns as $pattern) {
        if (preg_match($pattern, $relative)) {
            $skipped++;
            continue 2;
        }
    }

    $content = file_get_contents($path);
    $original = $content;

    foreach ($replacements as $pattern => $replacement) {
        if ($pattern === '/\bborder-dashed\b(?!\s+border-slate-300\s+dark:)/') {
            continue;
        }
        $content = preg_replace($pattern, $replacement, $content);
    }

    foreach ($gradientReplacements as $search => $replace) {
        if (str_contains($content, $search) && ! str_contains($content, 'dark:bg-gradient-to-br')) {
            $content = str_replace($search, $replace, $content);
        }
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        $updated++;
        echo "Updated: {$relative}\n";
    }
}

echo "\nDone. Updated {$updated} files, skipped {$skipped}.\n";
