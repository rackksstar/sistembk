<?php

namespace App\Support;

final class NavigationIcons
{
    public static function forRoute(string $route): string
    {
        return match (true) {
            str_contains($route, 'dashboard') => 'home',
            str_contains($route, 'chatbot') => 'chat',
            str_contains($route, 'consultations') => 'chat',
            str_contains($route, 'rapor') => 'document',
            str_contains($route, 'sekolah') => 'building',
            str_contains($route, 'kelas') => 'academic',
            str_contains($route, 'guru-bk') => 'academic',
            str_contains($route, 'master-pertanyaan') => 'question',
            str_contains($route, 'kategori-postingan') => 'tag',
            str_contains($route, 'postingan') => 'newspaper',
            str_contains($route, 'approvals') => 'check-badge',
            str_contains($route, 'users') => 'users',
            str_contains($route, 'students') => 'user',
            str_contains($route, 'guidance-classes') => 'rectangle-group',
            str_contains($route, 'guru-profile-changes') => 'pencil',
            str_contains($route, 'careers') => 'briefcase',
            str_contains($route, 'activity-logs') => 'clock',
            str_contains($route, 'profile') => 'user-circle',
            str_contains($route, 'penilaian') => 'star',
            str_contains($route, 'angket') => 'clipboard',
            str_contains($route, 'tryout') => 'puzzle',
            str_contains($route, 'instrument-questions') => 'beaker',
            str_contains($route, 'instrument-results') => 'beaker',
            str_contains($route, 'instruments') => 'beaker',
            str_contains($route, 'sociometry') => 'chart',
            str_contains($route, 'rpls') => 'book',
            str_contains($route, 'journals') => 'calendar',
            default => 'grid',
        };
    }
}
