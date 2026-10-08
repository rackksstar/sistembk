<?php

namespace Database\Seeders;

use App\Models\ConsultationRequest;
use App\Models\GuruBk;
use App\Models\Kelas;
use App\Models\ProgramStudi;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Sekolah;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Siapkan min. 3 sekolah + akun siswa/guru + sampel konsultasi prodi
 * untuk memenuhi catatan dosen: uji sistem di semua role & ≥3 sekolah.
 */
class MultiSchoolTestingSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            [
                'npsn' => '20260001',
                'nama' => 'SMA Negeri 1 Contoh',
                'alamat' => 'Jl. Pendidikan No. 10',
                'guru' => ['name' => 'Ibu Rina Guru BK', 'username' => '081234567890', 'email' => null],
                'siswa' => [
                    ['name' => 'Andi Uji Sekolah A', 'email' => 'uji.sekolah.a@bk.test', 'nisn' => '9100000001'],
                ],
            ],
            [
                'npsn' => '20260002',
                'nama' => 'SMA Negeri 2 Pekanbaru',
                'alamat' => 'Jl. Sudirman No. 22',
                'guru' => ['name' => 'Bapak Dedi Guru BK', 'username' => '081234567891', 'email' => 'dedi.guru@bk.test'],
                'siswa' => [
                    ['name' => 'Bunga Uji Sekolah B', 'email' => 'uji.sekolah.b1@bk.test', 'nisn' => '9100000002'],
                    ['name' => 'Citra Uji Sekolah B', 'email' => 'uji.sekolah.b2@bk.test', 'nisn' => '9100000003'],
                ],
            ],
            [
                'npsn' => '20260003',
                'nama' => 'SMK Negeri 1 Riau',
                'alamat' => 'Jl. Soekarno Hatta No. 5',
                'guru' => ['name' => 'Ibu Sari Guru BK', 'username' => '081234567892', 'email' => 'sari.guru@bk.test'],
                'siswa' => [
                    ['name' => 'Dimas Uji Sekolah C', 'email' => 'uji.sekolah.c1@bk.test', 'nisn' => '9100000004'],
                    ['name' => 'Eka Uji Sekolah C', 'email' => 'uji.sekolah.c2@bk.test', 'nisn' => '9100000005'],
                ],
            ],
        ];

        $prodi = ProgramStudi::query()
            ->active()
            ->orderByDesc('is_verified')
            ->orderBy('id')
            ->first();

        // Untuk demo uji: pastikan ada minimal 1 prodi aktif (verified jika memungkinkan).
        if ($prodi && ! $prodi->is_verified) {
            $prodi->update(['is_verified' => true]);
        }

        foreach ($schools as $index => $data) {
            $school = School::query()->updateOrCreate(
                ['npsn' => $data['npsn']],
                [
                    'name' => $data['nama'],
                    'address' => $data['alamat'],
                ]
            );

            $schoolClass = SchoolClass::query()->updateOrCreate(
                ['school_id' => $school->id, 'name' => 'XII Uji '.($index + 1)],
                ['level' => 'XII']
            );

            $sekolah = Sekolah::query()->updateOrCreate(
                ['npsn' => $data['npsn']],
                [
                    'nama' => $data['nama'],
                    'alamat' => $data['alamat'],
                    'is_mou' => true,
                    'paket_aktif' => 'Basic',
                    'tanggal_aktivasi' => now()->toDateString(),
                    'is_active' => true,
                ]
            );

            $jenjang = str_starts_with($data['nama'], 'SMK') ? 'SMK' : 'SMA';
            $kelas = Kelas::query()->updateOrCreate(
                ['sekolah_id' => $sekolah->id, 'nama' => 'XII Uji '.($index + 1)],
                [
                    'jenjang' => $jenjang,
                    'tingkatan' => 'XII',
                ]
            );

            $guruPayload = [
                'name' => $data['guru']['name'],
                'password' => Hash::make('password'),
                'school' => $school->name,
                'school_id' => $school->id,
                'role' => User::ROLE_GURU,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ];
            if ($data['guru']['email']) {
                $guruPayload['email'] = $data['guru']['email'];
            }

            $guru = User::query()->updateOrCreate(
                ['username' => $data['guru']['username']],
                $guruPayload
            );

            GuruBk::query()->updateOrCreate(
                ['user_id' => $guru->id],
                [
                    'sekolah_id' => $sekolah->id,
                    'no_hp' => $data['guru']['username'],
                    'jabatan' => 'Guru BK',
                ]
            );

            foreach ($data['siswa'] as $siswaData) {
                $siswaUser = User::query()->updateOrCreate(
                    ['email' => $siswaData['email']],
                    [
                        'name' => $siswaData['name'],
                        'password' => Hash::make('password'),
                        'school' => $school->name,
                        'school_id' => $school->id,
                        'class_id' => $schoolClass->id,
                        'role' => User::ROLE_SISWA,
                        'status' => User::STATUS_APPROVED,
                        'email_verified_at' => now(),
                    ]
                );

                Student::query()->updateOrCreate(
                    ['nisn' => $siswaData['nisn']],
                    [
                        'user_id' => $siswaUser->id,
                        'kelas_id' => $kelas->id,
                        'name' => $siswaUser->name,
                        'birth_date' => '2008-01-15',
                        'school' => $school->name,
                        'status_biodata' => 'lengkap',
                    ]
                );

                ConsultationRequest::query()->updateOrCreate(
                    [
                        'student_id' => $siswaUser->id,
                        'subject' => 'Konsultasi prodi uji — '.$sekolah->nama,
                    ],
                    [
                        'counselor_id' => $guru->id,
                        'case_category' => ConsultationRequest::CASE_PRODI_KULIAH,
                        'program_studi_id' => $prodi?->id,
                        'preferred_time' => 'Fleksibel — menunggu jadwal Guru BK',
                        'details' => $prodi
                            ? 'Ingin membahas kesesuaian minat dengan program studi '.$prodi->nama.' di PCR.'
                            : 'Ingin membahas pilihan program studi PCR bersama Guru BK.',
                        'status' => ConsultationRequest::STATUS_PENDING,
                    ]
                );
            }
        }
    }
}
