<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSekolahRequest;
use App\Http\Requests\Admin\UpdateSekolahRequest;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SekolahController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $active = $request->string('active')->toString();
        $mou = $request->string('mou')->toString();

        $sekolahs = Sekolah::query()
            ->when($mou !== '', fn ($q) => $q->where('is_mou', $mou === '1'))
            ->when($search, fn ($q) => $q->where(fn ($query) => $query
                ->where('nama', 'like', "%{$search}%")
                ->orWhere('npsn', 'like', "%{$search}%")
            ))
            ->when($active !== '', fn ($q) => $q->where('is_active', $active === '1'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.sekolah.index', compact('sekolahs', 'search', 'active', 'mou'));
    }

    public function store(StoreSekolahRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('sekolah-logos', 'public');
        }

        Sekolah::create($data);

        return back()->with('success', 'Sekolah berhasil dibuat.');
    }

    public function update(UpdateSekolahRequest $request, Sekolah $sekolah): RedirectResponse
    {
        $data = $request->validated();
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            if ($sekolah->logo_path) {
                Storage::disk('public')->delete($sekolah->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('sekolah-logos', 'public');
        }

        $sekolah->update($data);

        return back()->with('success', 'Sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah): RedirectResponse
    {
        if ($sekolah->logo_path) {
            Storage::disk('public')->delete($sekolah->logo_path);
        }

        $sekolah->delete();

        return back()->with('success', 'Sekolah berhasil dihapus.');
    }
}
