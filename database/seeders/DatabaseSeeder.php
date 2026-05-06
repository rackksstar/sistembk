<?php

namespace Database\Seeders;

use App\Models\ConsultationRequest;
use App\Models\CareerInfo;
use App\Models\GuruBk;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\GuidanceClass;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@bk.test'],
            [
                'name' => 'Admin BK',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'yola@gmail.com'],
            [
                'name' => 'yola',
                'password' => Hash::make('123456'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]
        );

        $guru = User::query()->updateOrCreate(
            ['email' => 'guru@bk.test'],
            [
                'name' => 'Ibu Rina Guru BK',
                'password' => Hash::make('password'),
                'role' => User::ROLE_GURU,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]
        );

        $siswa = User::query()->updateOrCreate(
            ['email' => 'siswa@bk.test'],
            [
                'name' => 'Andi Siswa',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SISWA,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]
        );

        $sekolah = Sekolah::query()->updateOrCreate(
            ['nama' => 'SMA Negeri 1 Contoh'],
            [
                'paket_aktif' => 'Basic',
                'tanggal_aktivasi' => now()->toDateString(),
                'is_active' => true,
            ]
        );

        $kelasXii = Kelas::query()->updateOrCreate(
            ['sekolah_id' => $sekolah->id, 'nama' => 'XII IPA 1'],
            [
                'jenjang' => 'SMA',
                'tingkatan' => 'XII',
            ]
        );

        GuruBk::query()->updateOrCreate(
            ['user_id' => $guru->id],
            [
                'sekolah_id' => $sekolah->id,
                'nip' => '1987654321001',
                'jabatan' => 'Guru BK',
                'bidang_studi' => 'Bimbingan Konseling',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'guru.pending@bk.test'],
            [
                'name' => 'Pak Dimas Guru Pending',
                'password' => Hash::make('password'),
                'role' => User::ROLE_GURU,
                'status' => User::STATUS_PENDING,
                'email_verified_at' => now(),
            ]
        );

        ConsultationRequest::query()->updateOrCreate(
            ['student_id' => $siswa->id, 'subject' => 'Persiapan ujian akhir'],
            [
                'counselor_id' => $guru->id,
                'preferred_time' => 'Senin pagi',
                'details' => 'Saya ingin berdiskusi tentang manajemen waktu belajar sebelum ujian.',
                'status' => ConsultationRequest::STATUS_APPROVED,
                'consultation_date' => now()->addDays(3)->toDateString(),
                'consultation_time' => '09:00',
                'scheduled_at' => now()->addDays(3)->setTime(9, 0),
                'notes' => 'Sesi awal sudah dijadwalkan.',
            ]
        );

        ConsultationRequest::query()->updateOrCreate(
            ['student_id' => $siswa->id, 'subject' => 'Konsultasi pemilihan jurusan'],
            [
                'counselor_id' => null,
                'preferred_time' => 'Rabu siang',
                'details' => 'Butuh arahan untuk memilih jurusan yang sesuai minat.',
                'status' => ConsultationRequest::STATUS_PENDING,
                'scheduled_at' => null,
                'notes' => null,
            ]
        );

        $studentProfile = Student::query()->updateOrCreate(
            ['nisn' => '0061234567'],
            [
                'user_id' => $siswa->id,
                'kelas_id' => $kelasXii->id,
                'name' => $siswa->name,
                'birth_date' => '2008-05-14',
                'school' => 'SMA Negeri 1 Contoh',
                'jenis_kelamin' => 'L',
                'status_biodata' => 'lengkap',
            ]
        );

        $guidanceClass = GuidanceClass::query()->updateOrCreate(
            ['name' => 'Kelas Bimbingan Karier XII'],
            [
                'code' => 'BK-KARIER',
                'description' => 'Kelompok bimbingan untuk persiapan studi lanjut dan pilihan karier.',
            ]
        );

        $guidanceClass->students()->syncWithoutDetaching([$studentProfile->id]);

        $extraStudents = [
            [
                'email' => 'siswa2@bk.test',
                'name' => 'Budi Siswa',
                'nisn' => '0061234568',
                'birth_date' => '2008-06-20',
            ],
            [
                'email' => 'siswa3@bk.test',
                'name' => 'Citra Siswa',
                'nisn' => '0061234569',
                'birth_date' => '2008-08-02',
            ],
        ];

        foreach ($extraStudents as $payload) {
            $user = User::query()->updateOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_SISWA,
                    'status' => User::STATUS_APPROVED,
                    'email_verified_at' => now(),
                ]
            );

            $profile = Student::query()->updateOrCreate(
                ['nisn' => $payload['nisn']],
                [
                    'user_id' => $user->id,
                    'kelas_id' => $kelasXii->id,
                    'name' => $user->name,
                    'birth_date' => $payload['birth_date'],
                    'school' => 'SMA Negeri 1 Contoh',
                    'status_biodata' => 'lengkap',
                ]
            );

            $guidanceClass->students()->syncWithoutDetaching([$profile->id]);
        }

        CareerInfo::query()->updateOrCreate(
            ['title' => 'Software Engineer'],
            [
                'description' => 'Merancang, membangun, dan memelihara aplikasi digital. Cocok untuk siswa yang senang logika, problem solving, dan teknologi.',
                'category' => 'Teknologi',
            ]
        );

        CareerInfo::query()->updateOrCreate(
            ['title' => 'Konselor Pendidikan'],
            [
                'description' => 'Membantu siswa memahami potensi diri, pilihan studi, serta strategi belajar yang lebih sehat dan terarah.',
                'category' => 'Pendidikan',
            ]
        );

        CareerInfo::query()->updateOrCreate(
            ['title' => 'Desainer UI/UX'],
            [
                'description' => 'Menciptakan pengalaman aplikasi yang mudah digunakan, indah, dan sesuai kebutuhan pengguna.',
                'category' => 'Kreatif',
            ]
        );

        unset($admin);
    }
}
