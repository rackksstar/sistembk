<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Support\AuthenticatedStudent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ChatbotController extends Controller
{
    public function index(): View
    {
        $student = AuthenticatedStudent::profile();

        return view('siswa.chatbot.index', [
            'studentId' => $student?->id ?? auth()->id(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $student = AuthenticatedStudent::profile();
        $serviceUrl = rtrim((string) config('services.konseling_chatbot.service_url'), '/');
        $token = (string) config('services.konseling_chatbot.token', '');
        $timeout = (int) config('services.konseling_chatbot.timeout', 30);

        if ($serviceUrl === '') {
            return response()->json([
                'reply' => 'Service chatbot belum dikonfigurasi. Hubungi admin sistem (CHATBOT_SERVICE_URL).',
            ], 500);
        }

        $headers = ['Accept' => 'application/json'];
        if ($token !== '') {
            $headers['X-Chatbot-Token'] = $token;
        }

        try {
            $response = Http::withHeaders($headers)
                ->acceptJson()
                ->asJson()
                ->timeout(max(5, $timeout))
                ->post($serviceUrl.'/chat', [
                    'message' => $validated['message'],
                    'studentId' => (string) ($student?->id ?? $request->user()->id),
                    'studentName' => (string) ($request->user()->name ?? ''),
                ]);
        } catch (ConnectionException $e) {
            Log::warning('chatbot.connection_failed', [
                'url' => $serviceUrl,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'reply' => 'Service chatbot sedang tidak dapat dihubungi. Pastikan layanan chatbot berjalan di server (VPS/cloud) dan URL-nya benar.',
            ], 502);
        } catch (\Throwable $e) {
            Log::error('chatbot.request_failed', [
                'url' => $serviceUrl,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'reply' => 'Terjadi gangguan saat menghubungi chatbot. Coba lagi sebentar.',
            ], 502);
        }

        if ($response->failed()) {
            $status = $response->status();
            $detail = $response->json('detail');

            if (is_array($detail)) {
                $detail = collect($detail)->flatten()->filter()->implode(' ');
            }

            $detailText = is_string($detail) && $detail !== '' ? $detail : null;

            if ($status === 401) {
                return response()->json([
                    'reply' => 'Autentikasi chatbot gagal. Periksa CHATBOT_API_TOKEN di Laravel dan service chatbot.',
                ], 502);
            }

            if ($status === 503) {
                return response()->json([
                    'reply' => $detailText
                        ?: 'Index chatbot belum siap. Admin perlu menjalankan ingest_buku.py pada service chatbot.',
                ], 502);
            }

            return response()->json([
                'reply' => $detailText
                    ? "Service chatbot mengembalikan status {$status}: {$detailText}"
                    : "Service chatbot mengembalikan status {$status}.",
            ], 502);
        }

        $reply = $response->json('reply');

        if (! is_string($reply) || blank($reply)) {
            return response()->json([
                'reply' => 'Service chatbot berhasil dipanggil, tetapi responsnya kosong.',
            ], 502);
        }

        return response()->json([
            'reply' => trim($reply),
        ]);
    }
}
