<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesInterestCategoryLinks;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreCareerFieldRequest extends FormRequest
{
    use ValidatesInterestCategoryLinks;

    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'contoh_pekerjaan_text' => ['nullable', 'string', 'max:5000'],
            'job_zone' => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_active' => ['required', 'boolean'],
            'categories' => ['required', 'array'],
            'categories.*' => ['array'],
            'categories.*.enabled' => ['nullable', 'boolean'],
            'categories.*.relevansi' => ['nullable', 'integer', 'min:1', 'max:3'],
        ];
    }

    public function withValidator($validator): void
    {
        $this->validateInterestCategoryLinks(
            $validator,
            'Pilih minimal satu kategori minat RIASEC beserta relevansinya agar bidang karier bisa direkomendasikan.'
        );
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama bidang karier wajib diisi.',
            'nama.max' => 'Nama bidang karier maksimal 150 karakter.',
            'deskripsi.max' => 'Deskripsi maksimal 5000 karakter.',
            'contoh_pekerjaan_text.max' => 'Contoh pekerjaan maksimal 5000 karakter.',
            'job_zone.integer' => 'Job zone harus berupa angka.',
            'job_zone.min' => 'Job zone minimal 1.',
            'job_zone.max' => 'Job zone maksimal 5.',
            'is_active.required' => 'Status aktif wajib dipilih.',
            'is_active.boolean' => 'Status aktif tidak valid.',
            'categories.required' => 'Pilih minimal satu kategori minat RIASEC.',
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
