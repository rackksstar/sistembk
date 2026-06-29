<?php

/**
 * Perbaikan cross-check dark mode — hover/focus state & duplikat kelas.
 */

$root = dirname(__DIR__);
$viewsPath = $root.'/resources/views';

$skipPatterns = [
    '/print\.blade\.php$/',
    '/pdf\.blade\.php$/',
];

$replacements = [
    'hover:bg-blue-50 dark:bg-blue-950/40' => 'hover:bg-blue-50 dark:hover:bg-blue-950/40',
    'hover:border-blue-200 dark:border-blue-800' => 'hover:border-blue-200 dark:hover:border-blue-800',
    'hover:bg-slate-50 dark:bg-slate-800' => 'hover:bg-slate-50 dark:hover:bg-slate-800',
    'hover:bg-slate-100 dark:bg-slate-800' => 'hover:bg-slate-100 dark:hover:bg-slate-800',
    'hover:bg-slate-100 dark:bg-slate-700' => 'hover:bg-slate-100 dark:hover:bg-slate-700',
    'hover:bg-slate-200 dark:bg-slate-700' => 'hover:bg-slate-200 dark:hover:bg-slate-700',
    'hover:bg-slate-200 dark:bg-slate-800' => 'hover:bg-slate-200 dark:hover:bg-slate-800',
    'focus:bg-slate-100 dark:bg-slate-800' => 'focus:bg-slate-100 dark:focus:bg-slate-800',
    'bg-white/85' => 'bg-white/85 dark:bg-slate-900/85',
    'bg-white/70' => 'bg-white/70 dark:bg-slate-900/70',
    'bg-white/80' => 'bg-white/80 dark:bg-slate-900/80',
    'dark:border-slate-700/80 dark:border-slate-700/80' => 'dark:border-slate-700/80',
    'dark:bg-slate-900/80 dark:bg-slate-900/80' => 'dark:bg-slate-900/80',
    'dark:border-slate-700 dark:bg-slate-900 dark:border-slate-700 dark:bg-slate-900' => 'dark:border-slate-700 dark:bg-slate-900',
    'dark:text-blue-300 dark:text-blue-300' => 'dark:text-blue-300',
    'dark:text-slate-300 dark:text-slate-300' => 'dark:text-slate-300',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath)
);

$updated = 0;

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    foreach ($skipPatterns as $pattern) {
        if (preg_match($pattern, $path)) {
            continue 2;
        }
    }

    $content = file_get_contents($path);
    $original = $content;

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        $updated++;
        echo 'Fixed: '.str_replace($root.'/', '', $path)."\n";
    }
}

echo "\nFixed {$updated} files.\n";
