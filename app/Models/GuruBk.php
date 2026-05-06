<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuruBk extends Model
{
    use HasFactory;

    protected $table = 'guru_bks';

    protected $fillable = [
        'user_id',
        'sekolah_id',
        'nip',
        'jabatan',
        'bidang_studi',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }
}

