<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponsAngket extends Model
{
    use HasFactory;

    protected $table = 'respons_angket';

    protected $fillable = [
        'student_id',
        'master_question_id',
        'jawaban',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function masterQuestion(): BelongsTo
    {
        return $this->belongsTo(MasterQuestion::class);
    }
}
