<?php

namespace App\Http\Requests\Admin;

use App\Models\InterestCategory;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCareerFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'contoh_pekerjaan_text' => ['nullable', 'string'],
            'job_zone' => ['nullable', 'integer', 'min:1', 'max:5'],
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
            'nama.required' => 'Nama bidang karier wajib diisi.',
            'nama.max' => 'Nama bidang karier maksimal 150 karakter.',
            'job_zone.integer' => 'Job zone harus berupa angka.',
            'job_zone.min' => 'Job zone minimal 1.',
            'job_zone.max' => 'Job zone maksimal 5.',
            'is_active.required' => 'Status aktif wajib dipilih.',
            'is_active.boolean' => 'Status aktif tidak valid.',
            'categories.*.relevansi.min' => 'Relevansi minimal 1.',
            'categories.*.relevansi.max' => 'Relevansi maksimal 3.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('job_zone')) {
            $this->merge(['job_zone' => null]);
        }
    }
}
