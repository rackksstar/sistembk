<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInterestCategoryRequest;
use App\Http\Requests\Admin\UpdateInterestCategoryRequest;
use App\Models\InterestCategory;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterestCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $categories = InterestCategory::query()
            ->withCount('questions')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode', 'like', "%{$search}%");
                });
            })
            ->orderBy('urutan')
            ->orderBy('kode')
            ->paginate(10)
            ->withQueryString();

        return view('admin.interest-categories.index', compact('categories', 'search'));
    }

    public function store(StoreInterestCategoryRequest $request): RedirectResponse
    {
        $category = InterestCategory::create($request->validated());

        ActivityLogger::log('interest-category.created', $category, ['nama' => $category->nama]);

        return back()->with('success', 'Kategori minat berhasil dibuat.');
    }

    public function update(UpdateInterestCategoryRequest $request, InterestCategory $interestCategory): RedirectResponse
    {
        $interestCategory->update($request->validated());

        ActivityLogger::log('interest-category.updated', $interestCategory, ['nama' => $interestCategory->nama]);

        return back()->with('success', 'Kategori minat berhasil diperbarui.');
    }

    public function destroy(InterestCategory $interestCategory): RedirectResponse
    {
        if ($interestCategory->questions()->withTrashed()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih dipakai soal (termasuk yang diarsip). Nonaktifkan saja.');
        }

        ActivityLogger::log('interest-category.deleted', $interestCategory, ['nama' => $interestCategory->nama]);

        $interestCategory->delete();

        return back()->with('success', 'Kategori minat berhasil dihapus.');
    }
}
