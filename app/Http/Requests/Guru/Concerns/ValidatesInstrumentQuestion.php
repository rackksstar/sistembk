<?php

namespace App\Http\Requests\Guru\Concerns;

use App\Models\InstrumentQuestion;
use App\Models\InterestCategory;
use Illuminate\Validation\Rule;

trait ValidatesInstrumentQuestion
{
    protected function prepareForValidation(): void
    {
        $isMinatBakat = $this->input('category') === InstrumentQuestion::CATEGORY_MINAT_BAKAT;
        $isStrategi = $this->input('category') === InstrumentQuestion::CATEGORY_GAYA_BELAJAR;
        $talentCode = strtoupper(trim((string) $this->input('talent_code', '')));
        $interestCategoryId = $this->input('interest_category_id');
        $section = $this->input('section');

        if ($isMinatBakat && $talentCode !== '' && array_key_exists($talentCode, InstrumentQuestion::RIASEC_CODES)) {
            $resolvedId = InterestCategory::query()
                ->where('kode', $talentCode)
                ->where(function ($query) use ($interestCategoryId) {
                    $query->where('is_active', true);
                    if ($interestCategoryId) {
                        $query->orWhere('id', $interestCategoryId);
                    }
                })
                ->value('id');

            if ($resolvedId) {
                $interestCategoryId = $resolvedId;
            }
        }

        if (! $isMinatBakat) {
            $talentCode = '';
            $interestCategoryId = null;
        }

        if (! $isStrategi || $section === '' || $section === null) {
            $section = null;
        }

        $this->merge([
            'talent_code' => $isMinatBakat && $talentCode !== '' ? $talentCode : null,
            'interest_category_id' => ! $isMinatBakat || $interestCategoryId === '' || $interestCategoryId === null
                ? null
                : $interestCategoryId,
            'section' => $section,
            'jenjang_target' => $this->input('jenjang_target') ?: 'semua',
            'bobot' => $this->filled('bobot') ? $this->input('bobot') : 1,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $strategiSections = array_keys(InstrumentQuestion::SECTIONS[InstrumentQuestion::CATEGORY_GAYA_BELAJAR] ?? []);

        return [
            'category' => ['required', Rule::in(array_keys(InstrumentQuestion::CATEGORIES))],
            'question' => ['required', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'section' => [
                'nullable',
                'required_if:category,'.InstrumentQuestion::CATEGORY_GAYA_BELAJAR,
                Rule::in($strategiSections),
            ],
            'talent_code' => [
                'nullable',
                'required_if:category,'.InstrumentQuestion::CATEGORY_MINAT_BAKAT,
                Rule::in(array_keys(InstrumentQuestion::RIASEC_CODES)),
            ],
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
            'section.required_if' => 'Bagian Strategi Belajar wajib dipilih.',
            'section.in' => 'Bagian Strategi Belajar tidak valid.',
            'talent_code.required_if' => 'Kode RIASEC (Talents Mapping) wajib dipilih untuk soal Minat Bakat Kuliah.',
            'talent_code.in' => 'Kode RIASEC tidak valid.',
            'interest_category_id.required_if' => 'Kategori minat wajib dipilih untuk soal Minat Bakat Kuliah.',
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
        $validated['section'] = isset($validated['section']) ? (int) $validated['section'] : null;

        if (($validated['category'] ?? null) !== InstrumentQuestion::CATEGORY_MINAT_BAKAT) {
            $validated['interest_category_id'] = null;
            $validated['talent_code'] = null;
        }

        if (($validated['category'] ?? null) !== InstrumentQuestion::CATEGORY_GAYA_BELAJAR) {
            $validated['section'] = null;
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
