<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSekolahRequest;
use App\Http\Requests\Admin\UpdateSekolahRequest;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SekolahController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $active = $request->string('active')->toString();

        $sekolahs = Sekolah::query()
            ->when($search, fn ($q) => $q->where('nama', 'like', "%{$search}%"))
            ->when($active !== '', fn ($q) => $q->where('is_active', $active === '1'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.sekolah.index', compact('sekolahs', 'search', 'active'));
    }

    public function store(StoreSekolahRequest $request): RedirectResponse
    {
        Sekolah::create($request->validated());

        return back()->with('success', 'Sekolah berhasil dibuat.');
    }

    public function update(UpdateSekolahRequest $request, Sekolah $sekolah): RedirectResponse
    {
        $sekolah->update($request->validated());

        return back()->with('success', 'Sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah): RedirectResponse
    {
        $sekolah->delete();

        return back()->with('success', 'Sekolah berhasil dihapus.');
    }
}

