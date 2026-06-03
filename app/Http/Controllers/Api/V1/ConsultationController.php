<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ConsultationRequest::query()
            ->with(['student:id,name,email', 'counselor:id,name,username'])
            ->latest();

        if ($request->user()->role === User::ROLE_GURU) {
            $query->where('counselor_id', $request->user()->id);
        }

        $items = $query->paginate(min(50, (int) $request->input('per_page', 15)));

        return response()->json($items);
    }
}
