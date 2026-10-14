<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_menerima_balasan_dari_service_chatbot(): void
    {
        config([
            'services.konseling_chatbot.service_url' => 'http://chatbot.test',
            'services.konseling_chatbot.token' => 'secret-token',
            'services.konseling_chatbot.timeout' => 10,
        ]);

        Http::fake([
            'http://chatbot.test/chat' => Http::response([
                'reply' => 'PCR punya jalur PSUD, UMPCR, Mandiri, dan Beasiswa.',
            ], 200),
        ]);

        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($siswa)
            ->postJson(route('siswa.chatbot.store'), [
                'message' => 'Jalur masuk PCR apa saja?',
            ])
            ->assertOk()
            ->assertJson([
                'reply' => 'PCR punya jalur PSUD, UMPCR, Mandiri, dan Beasiswa.',
            ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://chatbot.test/chat'
                && $request->hasHeader('X-Chatbot-Token', 'secret-token')
                && $request['message'] === 'Jalur masuk PCR apa saja?';
        });
    }

    public function test_siswa_mendapat_pesan_jelas_jika_service_down(): void
    {
        config([
            'services.konseling_chatbot.service_url' => 'http://chatbot.test',
            'services.konseling_chatbot.token' => '',
        ]);

        Http::fake([
            'http://chatbot.test/chat' => function () {
                throw new ConnectionException('Connection refused');
            },
        ]);

        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($siswa)
            ->postJson(route('siswa.chatbot.store'), [
                'message' => 'Halo',
            ])
            ->assertStatus(502)
            ->assertJsonFragment([
                'reply' => 'Service chatbot sedang tidak dapat dihubungi. Pastikan layanan chatbot berjalan di server (VPS/cloud) dan URL-nya benar.',
            ]);
    }

    public function test_pesan_kosong_ditolak_validasi(): void
    {
        $siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'status' => User::STATUS_APPROVED,
        ]);

        $this->actingAs($siswa)
            ->postJson(route('siswa.chatbot.store'), [
                'message' => '',
            ])
            ->assertStatus(422);
    }
}
