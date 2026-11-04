<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostinganRequest;
use App\Http\Requests\Admin\UpdatePostinganRequest;
use App\Models\PostCategory;
use App\Models\Postingan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\ActivityLogger;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostinganController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $kategoriId = $request->integer('kategori');
        $status = $request->string('status')->toString();

        $postingan = Postingan::query()
            ->with('kategori')
            ->when($search, fn ($q) => $q->where(fn ($query) => $query
                ->where('judul', 'like', "%{$search}%")
                ->orWhere('isi', 'like', "%{$search}%")
            ))
            ->when($kategoriId, fn ($q) => $q->where('post_category_id', $kategoriId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $kategoris = PostCategory::query()->orderBy('name')->get();

        return view('admin.postingan.index', compact('postingan', 'kategoris', 'search', 'kategoriId', 'status'));
    }

    public function store(StorePostinganRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['gambar']);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['judul']));

        if ($request->hasFile('gambar')) {
            $data['gambar_path'] = $request->file('gambar')->store('postingan', 'public');
        }

        $postingan = Postingan::create($data);

        ActivityLogger::log('postingan.created', $postingan);

        return back()->with('success', 'Postingan berhasil dibuat.');
    }

    public function update(UpdatePostinganRequest $request, Postingan $postingan): RedirectResponse
    {
        $data = $request->validated();
        unset($data['gambar']);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['judul']), $postingan->id);

        if ($request->hasFile('gambar')) {
            if ($postingan->gambar_path) {
                Storage::disk('public')->delete($postingan->gambar_path);
            }

            $data['gambar_path'] = $request->file('gambar')->store('postingan', 'public');
        }

        $postingan->update($data);

        ActivityLogger::log('postingan.updated', $postingan);

        return back()->with('success', 'Postingan berhasil diperbarui.');
    }

    public function destroy(Postingan $postingan): RedirectResponse
    {
        ActivityLogger::log('postingan.deleted', $postingan, ['judul' => $postingan->judul]);

        if ($postingan->gambar_path) {
            Storage::disk('public')->delete($postingan->gambar_path);
        }

        $postingan->delete();

        return back()->with('success', 'Postingan berhasil dihapus.');
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $suffix = 2;

        while (
            Postingan::query()
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
