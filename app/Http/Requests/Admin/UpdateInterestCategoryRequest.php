<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInterestCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'kode' => [
                'required',
                'string',
                'max:5',
                'regex:/^[A-Z]{1,5}$/',
                Rule::unique('interest_categories', 'kode')->ignore($this->route('interestCategory')),
            ],
            'nama' => ['required', 'string', 'max:120'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'warna' => ['nullable', 'string', 'max:20', 'regex:/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode kategori wajib diisi.',
            'kode.max' => 'Kode kategori maksimal 5 karakter.',
            'kode.regex' => 'Kode kategori harus huruf kapital A–Z (contoh: R, I, A).',
            'kode.unique' => 'Kode kategori sudah digunakan.',
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.max' => 'Nama kategori maksimal 120 karakter.',
            'deskripsi.max' => 'Deskripsi maksimal 2000 karakter.',
            'warna.max' => 'Warna maksimal 20 karakter.',
            'warna.regex' => 'Warna harus format hex (contoh: #f59e0b).',
            'urutan.integer' => 'Urutan harus berupa angka.',
            'is_active.required' => 'Status aktif wajib dipilih.',
            'is_active.boolean' => 'Status aktif tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $warna = trim((string) $this->input('warna', ''));
        if ($warna !== '' && ! str_starts_with($warna, '#')) {
            $warna = '#'.$warna;
        }

        $this->merge([
            'kode' => strtoupper(trim((string) $this->input('kode', ''))),
            'warna' => $warna !== '' ? $warna : null,
            'urutan' => $this->filled('urutan') ? $this->input('urutan') : 0,
        ]);
    }
}
