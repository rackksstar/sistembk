<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuruProfileChange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuruProfileChangeController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status', 'baru')->toString();

        $changes = GuruProfileChange::query()
            ->with('user.guruBkProfile.sekolah')
            ->when($status === 'baru', fn ($query) => $query->whereNull('reviewed_at'))
            ->when($status === 'dibaca', fn ($query) => $query->whereNotNull('reviewed_at'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.guru-profile-changes.index', compact('changes', 'status'));
    }

    public function markReviewed(GuruProfileChange $change): RedirectResponse
    {
        $change->update(['reviewed_at' => now()]);

        return back()->with('success', 'Perubahan profil ditandai sudah dibaca.');
    }
}
