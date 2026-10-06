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
        $search = $request->string('search')->toString();
        $kategori = $request->string('kategori')->toString();

        $consultations = ConsultationRequest::with([
            'student:id,name',
            'student.studentProfile:id,user_id,kelas_id',
            'student.studentProfile.kelas:id,nama',
            'counselor:id,name',
        ])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($kategori, fn ($query) => $query->where('case_category', $kategori))
            ->when($search, fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('subject', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('student.studentProfile', fn ($sp) => $sp->where('nisn', 'like', "%{$search}%"))
                    ->orWhereHas('counselor', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.consultations.index', [
            'consultations' => $consultations,
            'status' => $status,
            'statuses' => ConsultationRequest::filterableStatuses(),
            'caseCategories' => ConsultationRequest::CASE_CATEGORIES,
            'search' => $search,
            'kategori' => $kategori,
        ]);
    }
}
