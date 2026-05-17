<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\InstrumentQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstrumentQuestionController extends Controller
{
    /**
     * Menampilkan daftar soal instrumen
     */
    public function index(Request $request): View
    {
        // Mengambil filter kategori dari URL (jika ada)
        $category = $request->string('category')->toString();

        // Query data soal dengan filter kategori dan pagination
        $questions = InstrumentQuestion::query()
            ->when($category, fn ($query) => $query->where('category', $category))
            ->latest() // Urutkan dari yang terbaru
            ->paginate(10) // Batasi 10 data per halaman
            ->withQueryString(); // Jaga agar parameter pencarian tidak hilang saat pindah halaman

        return view('guru.instruments.questions.index', [
            'questions' => $questions,
            'categories' => InstrumentQuestion::CATEGORIES, // Ambil daftar kategori dari model
            'category' => $category,
        ]);
    }

    /**
     * Menyimpan soal baru ke database
     */
    public function store(Request $request): RedirectResponse
    {
        // Gabungkan data tervalidasi dengan ID user yang sedang login
        InstrumentQuestion::create($this->validatedData($request) + [
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Soal instrumen berhasil ditambahkan.');
    }

    /**
     * Memperbarui data soal yang sudah ada
     */
    public function update(Request $request, InstrumentQuestion $question): RedirectResponse
    {
        // Update model berdasarkan data yang sudah lolos validasi
        $question->update($this->validatedData($request));

        return back()->with('success', 'Soal instrumen berhasil diperbarui.');
    }

    /**
     * Menghapus soal dari database
     */
    public function destroy(InstrumentQuestion $question): RedirectResponse
    {
        $question->delete();

        return back()->with('success', 'Soal instrumen berhasil dihapus.');
    }

    /**
     * Fungsi pembantu untuk validasi input form
     * Dipakai di fungsi store dan update (biar gak nulis ulang/DRY)
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(InstrumentQuestion::CATEGORIES))],
            'question' => ['required', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'options' => ['required', 'array', 'min:2'], // Minimal harus ada 2 pilihan jawaban
            'options.*.label' => ['required', 'string', 'max:255'],
            'options.*.score' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        // Memastikan is_active bernilai boolean (default true jika kosong)
        $validated['is_active'] = $request->boolean('is_active', true);

        // Membersihkan dan memastikan format array options (biasanya disimpan sebagai JSON)
        $validated['options'] = collect($validated['options'])
            ->map(fn ($option) => [
                'label' => $option['label'],
                'score' => (int) $option['score'],
            ])
            ->values()
            ->all();

        return $validated;
    }
}
