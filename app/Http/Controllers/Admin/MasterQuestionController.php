<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMasterQuestionRequest;
use App\Http\Requests\Admin\UpdateMasterQuestionRequest;
use App\Models\MasterQuestion;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterQuestionController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $kategori = $request->string('kategori')->toString();
        $active = $request->string('active')->toString();

        $questions = MasterQuestion::query()
            ->when($search, fn ($q) => $q->where('teks_pertanyaan', 'like', "%{$search}%"))
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->when($active !== '', fn ($q) => $q->where('is_active', $active === '1'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.master-pertanyaan.index', [
            'questions' => $questions,
            'kategoriOptions' => MasterQuestion::KATEGORI,
            'tipeOptions' => MasterQuestion::TIPE_INPUT,
            'search' => $search,
            'kategori' => $kategori,
            'active' => $active,
        ]);
    }

    public function store(StoreMasterQuestionRequest $request): RedirectResponse
    {
        $pertanyaan = MasterQuestion::create($request->validated());

        ActivityLogger::log('master-pertanyaan.created', $pertanyaan, ['kategori' => $pertanyaan->kategori]);

        return back()->with('success', 'Pertanyaan berhasil dibuat.');
    }

    public function update(UpdateMasterQuestionRequest $request, MasterQuestion $masterPertanyaan): RedirectResponse
    {
        $masterPertanyaan->update($request->validated());

        ActivityLogger::log('master-pertanyaan.updated', $masterPertanyaan, ['kategori' => $masterPertanyaan->kategori]);

        return back()->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function destroy(MasterQuestion $masterPertanyaan): RedirectResponse
    {
        ActivityLogger::log('master-pertanyaan.deleted', $masterPertanyaan, [
            'teks' => \Illuminate\Support\Str::limit($masterPertanyaan->teks, 80),
        ]);

        $masterPertanyaan->delete();

        return back()->with('success', 'Pertanyaan berhasil dihapus.');
    }
}

