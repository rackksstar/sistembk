<?php

namespace App\Http\Requests\Admin;

use App\Models\MasterQuestion;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMasterQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, [User::ROLE_ADMIN, User::ROLE_GURU], true);
    }

    protected function prepareForValidation(): void
    {
        $opsi = collect($this->input('opsi', []))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        $this->merge([
            'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'opsi' => $this->input('tipe_input') === MasterQuestion::TIPE_PILIHAN_GANDA ? $opsi : null,
            'kunci_jawaban' => $this->input('tipe_input') === MasterQuestion::TIPE_PILIHAN_GANDA
                ? ($this->input('kunci_jawaban') ?: null)
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'kategori' => ['required', 'string', 'in:'.implode(',', MasterQuestion::KATEGORI)],
            'teks_pertanyaan' => ['required', 'string', 'max:2000'],
            'tipe_input' => ['required', 'string', 'in:'.implode(',', MasterQuestion::TIPE_INPUT)],
            'opsi' => ['nullable', 'array'],
            'opsi.*' => ['nullable', 'string', 'max:255'],
            'kunci_jawaban' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('tipe_input') !== MasterQuestion::TIPE_PILIHAN_GANDA) {
                return;
            }

            $opsi = collect($this->input('opsi', []))->filter()->values();

            if ($opsi->count() < 2) {
                $validator->errors()->add('opsi', 'Pilihan ganda minimal punya 2 opsi.');
            }

            $kunci = $this->input('kunci_jawaban');
            if ($kunci && ! $opsi->contains($kunci)) {
                $validator->errors()->add('kunci_jawaban', 'Kunci jawaban harus salah satu opsi.');
            }
        });
    }
}
