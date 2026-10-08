<?php

namespace Tests\Feature\Phase8;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_api_mengembalikan_token(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
            'password' => bcrypt('password123'),
        ]);

        $this->postJson('/api/v1/login', [
            'role' => 'admin',
            'login' => $user->email,
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'role']]);
    }

    public function test_me_dan_logout_dengan_sanctum(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
            'username' => '081234567890',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $this->postJson('/api/v1/logout')
            ->assertOk();
    }

    public function test_guru_dapat_akses_daftar_konseling_api(): void
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_APPROVED,
        ]);

        Sanctum::actingAs($guru);

        $this->getJson('/api/v1/consultations')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_login_api_siswa_menggunakan_nisn_dan_tanggal_lahir(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        Student::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'nisn' => '1234567890',
            'birth_date' => '2010-05-15',
        ]);

        $this->postJson('/api/v1/login', [
            'role' => 'siswa',
            'login' => '1234567890',
            'password' => '2010-05-15',
        ])
            ->assertOk()
            ->assertJsonPath('user.role', User::ROLE_SISWA);
    }
}
