<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProgramStudiRequest;
use App\Http\Requests\Admin\UpdateProgramStudiRequest;
use App\Models\InterestCategory;
use App\Models\ProgramStudi;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramStudiController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $jurusan = $request->string('jurusan')->toString();
        $verified = $request->string('verified')->toString();
        $active = $request->string('active')->toString();

        $programStudis = ProgramStudi::query()
            ->with('interestCategories')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('nama', 'like', "%{$search}%")
                        ->orWhere('institusi', 'like', "%{$search}%")
                        ->orWhere('jurusan', 'like', "%{$search}%");
                });
            })
            ->when($jurusan, fn ($q) => $q->where('jurusan', $jurusan))
            ->when($verified !== '', fn ($q) => $q->where('is_verified', $verified === '1'))
            ->when($active !== '', fn ($q) => $q->where('is_active', $active === '1'))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $jurusanOptions = ProgramStudi::query()
            ->select('jurusan')
            ->whereNotNull('jurusan')
            ->where('jurusan', '!=', '')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan');

        $interestCategories = InterestCategory::query()
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get();

        return view('admin.program-studi.index', compact(
            'programStudis',
            'interestCategories',
            'jurusanOptions',
            'search',
            'jurusan',
            'verified',
            'active',
        ));
    }

    public function store(StoreProgramStudiRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('categories')->all();
        $programStudi = ProgramStudi::create($data);
        $programStudi->interestCategories()->sync($this->syncPayload($request->validated('categories', [])));

        ActivityLogger::log('program-studi.created', $programStudi, ['nama' => $programStudi->nama]);

        return back()->with('success', 'Program studi berhasil dibuat.');
    }

    public function update(UpdateProgramStudiRequest $request, ProgramStudi $programStudi): RedirectResponse
    {
        $data = collect($request->validated())->except('categories')->all();
        $programStudi->update($data);
        $programStudi->interestCategories()->sync($this->syncPayload($request->validated('categories', [])));

        ActivityLogger::log('program-studi.updated', $programStudi, ['nama' => $programStudi->nama]);

        return back()->with('success', 'Program studi berhasil diperbarui.');
    }

    public function destroy(ProgramStudi $programStudi): RedirectResponse
    {
        ActivityLogger::log('program-studi.deleted', $programStudi, ['nama' => $programStudi->nama]);

        $programStudi->interestCategories()->detach();
        $programStudi->delete();

        return back()->with('success', 'Program studi berhasil dihapus.');
    }

    /**
     * @param  array<string, array{enabled?: mixed, relevansi?: mixed}>  $categories
     * @return array<int, array{relevansi: int}>
     */
    private function syncPayload(array $categories): array
    {
        $sync = [];

        foreach ($categories as $id => $data) {
            if (empty($data['enabled'])) {
                continue;
            }

            $sync[(int) $id] = [
                'relevansi' => (int) ($data['relevansi'] ?? 2),
            ];
        }

        return $sync;
    }
}
