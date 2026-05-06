<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKelasRequest;
use App\Http\Requests\Admin\UpdateKelasRequest;
use App\Models\Kelas;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $sekolahId = $request->string('sekolah_id')->toString();
        $jenjang = $request->string('jenjang')->toString();

        $kelas = Kelas::query()
            ->with('sekolah')
            ->when($search, fn ($q) => $q->where('nama', 'like', "%{$search}%"))
            ->when($sekolahId, fn ($q) => $q->where('sekolah_id', $sekolahId))
            ->when($jenjang, fn ($q) => $q->where('jenjang', $jenjang))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $sekolahs = Sekolah::orderBy('nama')->get();
        $jenjangOptions = Kelas::query()->select('jenjang')->distinct()->whereNotNull('jenjang')->orderBy('jenjang')->pluck('jenjang');

        return view('admin.kelas.index', compact('kelas', 'sekolahs', 'jenjangOptions', 'search', 'sekolahId', 'jenjang'));
    }

    public function store(StoreKelasRequest $request): RedirectResponse
    {
        Kelas::create($request->validated());

        return back()->with('success', 'Kelas berhasil dibuat.');
    }

    public function update(UpdateKelasRequest $request, Kelas $kelas): RedirectResponse
    {
        $kelas->update($request->validated());

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $kelas->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}

