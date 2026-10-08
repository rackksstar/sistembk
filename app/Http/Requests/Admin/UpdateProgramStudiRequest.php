<?php

namespace App\Http\Requests\Admin;

use App\Models\InterestCategory;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgramStudiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'institusi' => ['required', 'string', 'max:150'],
            'nama' => [
                'required',
                'string',
                'max:150',
                Rule::unique('program_studis', 'nama')
                    ->where(fn ($q) => $q
                        ->where('institusi', $this->input('institusi'))
                        ->where('jenjang_pendidikan', $this->input('jenjang_pendidikan')))
                    ->ignore($this->route('programStudi')),
            ],
            'jenjang_pendidikan' => ['required', 'string', Rule::in(['D3', 'D4'])],
            'jurusan' => ['nullable', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'prospek_karier' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'is_verified' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['array'],
            'categories.*.enabled' => ['nullable', 'boolean'],
            'categories.*.relevansi' => ['nullable', 'integer', 'min:1', 'max:3'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $ids = collect($this->input('categories', []))
                ->filter(fn ($row) => ! empty($row['enabled']))
                ->keys()
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values();

            if ($ids->isEmpty()) {
                return;
            }

            $validCount = InterestCategory::query()
                ->whereIn('id', $ids)
                ->count();

            if ($validCount !== $ids->count()) {
                $validator->errors()->add('categories', 'Salah satu kategori minat tidak valid.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'institusi.required' => 'Institusi wajib diisi.',
            'nama.required' => 'Nama program studi wajib diisi.',
            'nama.unique' => 'Program studi dengan institusi dan jenjang ini sudah ada.',
            'jenjang_pendidikan.required' => 'Jenjang pendidikan wajib dipilih.',
            'jenjang_pendidikan.in' => 'Jenjang pendidikan harus D3 atau D4.',
            'website_url.url' => 'URL website tidak valid.',
            'is_verified.required' => 'Status verifikasi wajib dipilih.',
            'is_active.required' => 'Status aktif wajib dipilih.',
            'categories.*.relevansi.min' => 'Relevansi minimal 1.',
            'categories.*.relevansi.max' => 'Relevansi maksimal 3.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'institusi' => $this->input('institusi') ?: 'Politeknik Caltex Riau',
        ]);
    }
}
