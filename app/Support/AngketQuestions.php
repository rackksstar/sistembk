<?php

namespace App\Support;

use App\Models\MasterQuestion;
use Illuminate\Support\Collection;

class AngketQuestions
{
    /**
     * @return Collection<int, int>
     */
    public static function activeIds(): Collection
    {
        return once(function () {
            return MasterQuestion::query()
                ->where('kategori', MasterQuestion::KATEGORI_ANGKET)
                ->where('is_active', true)
                ->orderBy('id')
                ->pluck('id');
        });
    }

    public static function activeCount(): int
    {
        return self::activeIds()->count();
    }
}
