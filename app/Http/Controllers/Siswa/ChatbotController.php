<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Support\AuthenticatedStudent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        if ($serviceUrl === '') {
            return response()->json([
                'reply' => 'Service chatbot belum dikonfigurasi.',
            ], 500);
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(30)
                ->post($serviceUrl.'/chat', [
                    'message' => $validated['message'],
                    'studentId' => (string) ($student?->id ?? $request->user()->id),
                ]);
        } catch (\Throwable) {
            return response()->json([
                'reply' => 'Service chatbot belum bisa dihubungi. Pastikan FastAPI berjalan di server lokal.',
            ], 502);
        }

        if ($response->failed()) {
            $status = $response->status();
            $detail = $response->json('detail');

            return response()->json([
                'reply' => $detail
                    ? "Service chatbot mengembalikan status {$status}: {$detail}"
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
