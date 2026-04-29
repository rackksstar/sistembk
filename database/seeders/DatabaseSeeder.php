<?php

namespace Database\Seeders;

use App\Models\ConsultationRequest;
use App\Models\CareerInfo;
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
                'name' => $siswa->name,
                'birth_date' => '2008-05-14',
                'school' => 'SMA Negeri 1 Contoh',
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

        User::factory()
            ->count(6)
            ->sequence(
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED],
                ['role' => User::ROLE_SISWA, 'status' => User::STATUS_APPROVED],
                ['role' => User::ROLE_GURU, 'status' => User::STATUS_APPROVED],
            )
            ->create();

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
