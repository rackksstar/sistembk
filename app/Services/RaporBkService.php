<?php

namespace App\Services;

use App\Models\RaporBk;
use App\Models\Student;

class RaporBkService
{
    /**
     * @param  array{
     *     perkembangan_akademik?: ?string,
     *     perkembangan_sosial?: ?string,
     *     perkembangan_psikologis?: ?string,
     *     saran_tindak_lanjut?: ?string,
     *     catatan_guru?: ?string,
     *     status?: string
     * }  $data
     */
    public function upsertForStudent(
        int $counselorId,
        Student $student,
        string $semester,
        string $tahunAjaran,
        array $data
    ): RaporBk {
        return RaporBk::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'counselor_id' => $counselorId,
                'semester' => $semester,
                'tahun_ajaran' => $tahunAjaran,
            ],
            [
                'perkembangan_akademik' => $data['perkembangan_akademik'] ?? null,
                'perkembangan_sosial' => $data['perkembangan_sosial'] ?? null,
                'perkembangan_psikologis' => $data['perkembangan_psikologis'] ?? null,
                'saran_tindak_lanjut' => $data['saran_tindak_lanjut'] ?? null,
                'catatan_guru' => $data['catatan_guru'] ?? null,
                'status' => $data['status'] ?? RaporBk::STATUS_DRAFT,
            ]
        );
    }
}
