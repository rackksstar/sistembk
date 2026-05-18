<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rpl extends Model
{
    public const TYPE_INDIVIDU = 'individu';
    public const TYPE_KELOMPOK = 'kelompok';

    public const TYPES = [
        self::TYPE_INDIVIDU => 'Individu',
        self::TYPE_KELOMPOK => 'Kelompok',
    ];

    protected $fillable = [
        'teacher_id',
        'title',
        'type',
        'service_date',
        'target',
        'tujuan',
        'materi',
        'metode',
        'evaluasi',
    ];

    protected function casts(): array
    {
        return ['service_date' => 'date'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
