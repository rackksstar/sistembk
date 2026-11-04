<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Models\Postingan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostinganController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $kategoriId = $request->integer('kategori');

        $postingan = Postingan::query()
            ->with('kategori')
            ->where('status', Postingan::STATUS_PUBLISHED)
            ->when($search, fn ($q) => $q->where(fn ($query) => $query
                ->where('judul', 'like', "%{$search}%")
                ->orWhere('isi', 'like', "%{$search}%")
            ))
            ->when($kategoriId, fn ($q) => $q->where('post_category_id', $kategoriId))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $kategoris = PostCategory::query()
            ->whereHas('postingan', fn ($q) => $q->where('status', Postingan::STATUS_PUBLISHED))
            ->orderBy('name')
            ->get();

        return view('siswa.postingan.index', compact('postingan', 'kategoris', 'search', 'kategoriId'));
    }

    public function show(Postingan $postingan): View
    {
        abort_unless($postingan->isPublished(), 404);

        $postingan->load('kategori');

        return view('siswa.postingan.show', compact('postingan'));
    }
}
