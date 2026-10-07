<?php

namespace App\Http\Requests\Guru\Concerns;

use App\Models\InstrumentQuestion;
use Illuminate\Validation\Rule;

trait ValidatesInstrumentQuestion
{
    protected function prepareForValidation(): void
    {
        $isMinatBakat = $this->input('category') === InstrumentQuestion::CATEGORY_MINAT_BAKAT;
        $interestCategoryId = $this->input('interest_category_id');

        $this->merge([
            'interest_category_id' => ! $isMinatBakat || $interestCategoryId === '' || $interestCategoryId === null
                ? null
                : $interestCategoryId,
            'jenjang_target' => $this->input('jenjang_target') ?: 'semua',
            'bobot' => $this->filled('bobot') ? $this->input('bobot') : 1,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(array_keys(InstrumentQuestion::CATEGORIES))],
            'question' => ['required', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'interest_category_id' => [
                'nullable',
                'required_if:category,'.InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                Rule::exists('interest_categories', 'id')->where(function ($query) {
                    $currentId = $this->route('question')?->interest_category_id;
                    $query->where(function ($inner) use ($currentId) {
                        $inner->where('is_active', 1);
                        if ($currentId) {
                            $inner->orWhere('id', $currentId);
                        }
                    });
                }),
            ],
            'jenjang_target' => ['nullable', Rule::in(InstrumentQuestion::JENJANG_TARGETS)],
            'bobot' => ['nullable', 'integer', 'min:1', 'max:5'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*.label' => ['required', 'string', 'max:255'],
            'options.*.score' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Jenis instrumen wajib dipilih.',
            'category.in' => 'Jenis instrumen tidak valid.',
            'question.required' => 'Teks soal wajib diisi.',
            'question.max' => 'Teks soal maksimal 1000 karakter.',
            'interest_category_id.required_if' => 'Kategori minat wajib dipilih untuk soal Minat Bakat.',
            'interest_category_id.exists' => 'Kategori minat tidak ditemukan atau tidak aktif.',
            'jenjang_target.in' => 'Target jenjang harus semua, SMA, atau SMK.',
            'bobot.integer' => 'Bobot harus berupa angka.',
            'bobot.min' => 'Bobot minimal 1.',
            'bobot.max' => 'Bobot maksimal 5.',
            'options.required' => 'Pilihan jawaban wajib diisi.',
            'options.min' => 'Minimal harus ada 2 pilihan jawaban.',
            'options.max' => 'Maksimal 6 pilihan jawaban.',
            'options.*.label.required' => 'Label pilihan jawaban wajib diisi.',
            'options.*.label.max' => 'Label pilihan jawaban maksimal 255 karakter.',
            'options.*.score.required' => 'Skor pilihan jawaban wajib diisi.',
            'options.*.score.integer' => 'Skor pilihan jawaban harus berupa angka.',
            'options.*.score.min' => 'Skor pilihan jawaban minimal 0.',
            'options.*.score.max' => 'Skor pilihan jawaban maksimal 100.',
        ];
    }

    public function instrumentQuestionData(): array
    {
        $validated = $this->validated();

        $validated['is_active'] = $this->boolean('is_active');
        $validated['jenjang_target'] = $validated['jenjang_target'] ?? 'semua';
        $validated['bobot'] = (int) ($validated['bobot'] ?? 1);

        if (($validated['category'] ?? null) !== InstrumentQuestion::CATEGORY_MINAT_BAKAT) {
            $validated['interest_category_id'] = $validated['interest_category_id'] ?? null;
        }

        $validated['options'] = collect($validated['options'])
            ->map(fn (array $option) => [
                'label' => $option['label'],
                'score' => (int) $option['score'],
            ])
            ->values()
            ->all();

        return $validated;
    }
}
