<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status', User::STATUS_PENDING)->toString();

        $teachers = User::query()
            ->where('role', User::ROLE_GURU)
            ->when($status !== 'semua', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.approvals.index', compact('teachers', 'status'));
    }

    public function approve(User $user): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_GURU, 404);

        $user->update(['status' => User::STATUS_APPROVED]);

        return back()->with('success', 'Pendaftaran Guru BK berhasil disetujui.');
    }

    public function reject(User $user): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_GURU, 404);

        $user->update(['status' => User::STATUS_REJECTED]);

        return back()->with('success', 'Pendaftaran Guru BK berhasil ditolak.');
    }
}
