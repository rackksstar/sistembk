<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostCategoryRequest;
use App\Http\Requests\Admin\UpdatePostCategoryRequest;
use App\Models\PostCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $categories = PostCategory::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.kategori-postingan.index', compact('categories', 'search'));
    }

    public function store(StorePostCategoryRequest $request): RedirectResponse
    {
        $name = $request->validated('name');
        $slug = Str::slug($name);

        PostCategory::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($slug),
        ]);

        return back()->with('success', 'Kategori postingan berhasil dibuat.');
    }

    public function update(UpdatePostCategoryRequest $request, PostCategory $kategoriPostingan): RedirectResponse
    {
        $name = $request->validated('name');
        $slug = Str::slug($name);

        $kategoriPostingan->update([
            'name' => $name,
            'slug' => $this->uniqueSlug($slug, $kategoriPostingan->id),
        ]);

        return back()->with('success', 'Kategori postingan berhasil diperbarui.');
    }

    public function destroy(PostCategory $kategoriPostingan): RedirectResponse
    {
        $kategoriPostingan->delete();

        return back()->with('success', 'Kategori postingan berhasil dihapus.');
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $suffix = 2;

        while (
            PostCategory::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}

