<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCareerFieldRequest;
use App\Http\Requests\Admin\UpdateCareerFieldRequest;
use App\Models\CareerField;
use App\Models\InterestCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerFieldController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $active = $request->string('active')->toString();

        $careerFields = CareerField::query()
            ->with('interestCategories')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('nama', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            })
            ->when($active !== '', fn ($q) => $q->where('is_active', $active === '1'))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $interestCategories = InterestCategory::query()
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get();

        return view('admin.bidang-karier.index', compact(
            'careerFields',
            'interestCategories',
            'search',
            'active',
        ));
    }

    public function store(StoreCareerFieldRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except(['categories', 'contoh_pekerjaan_text'])->all();
        $data['contoh_pekerjaan'] = $this->parseJobs($request->validated('contoh_pekerjaan_text'));

        $careerField = CareerField::create($data);
        $careerField->interestCategories()->sync($this->syncPayload($request->validated('categories', [])));

        ActivityLogger::log('bidang-karier.created', $careerField, ['nama' => $careerField->nama]);

        return back()->with('success', 'Bidang karier berhasil dibuat.');
    }

    public function update(UpdateCareerFieldRequest $request, CareerField $careerField): RedirectResponse
    {
        $data = collect($request->validated())->except(['categories', 'contoh_pekerjaan_text'])->all();
        $data['contoh_pekerjaan'] = $this->parseJobs($request->validated('contoh_pekerjaan_text'));

        $careerField->update($data);
        $careerField->interestCategories()->sync($this->syncPayload($request->validated('categories', [])));

        ActivityLogger::log('bidang-karier.updated', $careerField, ['nama' => $careerField->nama]);

        return back()->with('success', 'Bidang karier berhasil diperbarui.');
    }

    public function destroy(CareerField $careerField): RedirectResponse
    {
        ActivityLogger::log('bidang-karier.deleted', $careerField, ['nama' => $careerField->nama]);

        $careerField->interestCategories()->detach();
        $careerField->delete();

        return back()->with('success', 'Bidang karier berhasil dihapus.');
    }

    /**
     * @return list<string>
     */
    private function parseJobs(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
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
