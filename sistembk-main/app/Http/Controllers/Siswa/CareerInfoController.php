<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\CareerInfo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerInfoController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $category = $request->string('category')->toString();

        $careers = CareerInfo::query()
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $categories = CareerInfo::select('category')->distinct()->orderBy('category')->pluck('category');

        return view('siswa.careers.index', compact('careers', 'categories', 'search', 'category'));
    }
}
