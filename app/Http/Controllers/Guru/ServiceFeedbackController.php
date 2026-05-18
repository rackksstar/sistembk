<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ServiceFeedback;
use Illuminate\View\View;

class ServiceFeedbackController extends Controller
{
    public function index(): View
    {
        $feedback = ServiceFeedback::query()
            ->with(['student:id,name,class_id,school_id', 'student.classModel:id,name', 'student.schoolModel:id,name', 'consultation:id,subject'])
            ->latest()
            ->paginate(12);

        return view('guru.feedback.index', compact('feedback'));
    }
}
