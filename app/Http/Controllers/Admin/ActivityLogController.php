<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $logs = ActivityLog::query()
            ->with('user:id,name,role,email,username')
            ->latest()
            ->paginate(25);

        return view('admin.activity-logs.index', compact('logs'));
    }
}
