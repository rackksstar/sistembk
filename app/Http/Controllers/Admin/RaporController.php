<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RaporBk;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RaporController extends Controller
{
    public function index(Request $request): View
    {
        $semester = $request->string('semester')->toString();
        $tahunAjaran = $request->string('tahun_ajaran')->toString();
        $status = $request->string('status')->toString();

        $rapor = RaporBk::query()
            ->with([
                'student:id,name,nisn,kelas_id,user_id',
                'student.user:id,name',
                'student.kelas:id,nama',
                'counselor:id,name',
            ])
            ->when($semester, fn ($q) => $q->where('semester', $semester))
            ->when($tahunAjaran, fn ($q) => $q->where('tahun_ajaran', $tahunAjaran))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.rapor.index', compact('rapor', 'semester', 'tahunAjaran', 'status'));
    }

    public function show(RaporBk $rapor): View
    {
        $rapor->load([
            'student.user',
            'student.kelas',
            'counselor',
        ]);

        return view('admin.rapor.show', compact('rapor'));
    }
}
