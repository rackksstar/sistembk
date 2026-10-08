<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TryOutDetail extends Model
{
    use HasFactory;

    protected $table = 'try_out_detail';

    protected $fillable = [
        'try_out_id',
        'student_id',
        'jawaban',
        'rata_skor',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'jawaban' => 'array',
            'rata_skor' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    public function tryOut(): BelongsTo
    {
        return $this->belongsTo(TryOut::class, 'try_out_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
