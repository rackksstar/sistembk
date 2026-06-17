<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\HasilInstrumen;
use App\Models\PertanyaanInstrumen;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HasilInstrumenController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('category')->toString();

        $submissions = HasilInstrumen::query()
            ->with(['student:id,name,email,school,school_id,class_id', 'student.schoolModel:id,name', 'student.classModel:id,name', 'answers.pertanyaan:id,question'])
            ->when($category, fn ($query) => $query->where('category', $category))
            ->latest('submitted_at')
            ->paginate(12)
            ->withQueryString();

        return view('guru.instruments.results.index', [
            'submissions' => $submissions,
            'categories' => PertanyaanInstrumen::CATEGORIES,
            'category' => $category,
        ]);
    }
}
