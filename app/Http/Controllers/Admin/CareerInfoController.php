<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCareerInfoRequest;
use App\Http\Requests\Admin\UpdateCareerInfoRequest;
use App\Models\CareerInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CareerInfoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $category = $request->string('category')->toString();

        $careers = CareerInfo::query()
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($category, fn ($query) => $query->where('category', $category))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $categories = CareerInfo::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.careers.index', compact('careers', 'categories', 'search', 'category'));
    }

    public function store(StoreCareerInfoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('career-infos', 'public');
        }

        unset($data['image']);

        CareerInfo::create($data);

        return back()->with('success', 'Informasi karier berhasil dibuat.');
    }

    public function update(UpdateCareerInfoRequest $request, CareerInfo $career): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($career->image_path) {
                Storage::disk('public')->delete($career->image_path);
            }

            $data['image_path'] = $request->file('image')->store('career-infos', 'public');
        }

        unset($data['image']);

        $career->update($data);

        return back()->with('success', 'Informasi karier berhasil diperbarui.');
    }

    public function destroy(CareerInfo $career): RedirectResponse
    {
        if ($career->image_path) {
            Storage::disk('public')->delete($career->image_path);
        }

        $career->delete();

        return back()->with('success', 'Informasi karier berhasil dihapus.');
    }
}
