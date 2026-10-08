<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $consultations = ConsultationRequest::with([
            'student:id,name',
            'student.studentProfile:id,user_id,kelas_id',
            'student.studentProfile.kelas:id,nama',
            'counselor:id,name',
        ])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.consultations.index', [
            'consultations' => $consultations,
            'status' => $status,
            'statuses' => ConsultationRequest::filterableStatuses(),
        ]);
    }
}
