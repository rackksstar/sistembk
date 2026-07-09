<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiswaSmk extends Model
{
    use HasFactory;

    protected $table = 'siswa_smk';

    protected $fillable = [
        'student_id',
        'user_id',
        'name',
        'nisn',
        'sekolah',
        'jurusan',
        'kelas',
        'tahun_lulus',
        'nomor_hp',
        'email',
        'alamat',
        'keahlian',
        'pengalaman',
        'status_kerja',
        'siap_dihubungi',
    ];

    protected function casts(): array
    {
        return [
            'keahlian' => 'array',
            'siap_dihubungi' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
