<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TryOut extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_AKTIF = 'aktif';

    public const STATUS_SELESAI = 'selesai';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_AKTIF => 'Aktif',
        self::STATUS_SELESAI => 'Selesai',
    ];

    protected $table = 'try_outs';

    protected $fillable = [
        'counselor_id',
        'judul',
        'deskripsi',
        'durasi_menit',
        'mulai_at',
        'selesai_at',
        'soal_ids',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'durasi_menit' => 'integer',
            'mulai_at' => 'datetime',
            'selesai_at' => 'datetime',
            'soal_ids' => 'array',
        ];
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'try_out_kelas', 'try_out_id', 'kelas_id')
            ->withTimestamps();
    }

    public function details(): HasMany
    {
        return $this->hasMany(TryOutDetail::class, 'try_out_id');
    }

    public function isActiveNow(): bool
    {
        if ($this->status !== self::STATUS_AKTIF) {
            return false;
        }

        $now = now();

        return $now->between($this->mulai_at, $this->selesai_at);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
