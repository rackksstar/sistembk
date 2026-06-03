<?php

namespace App\Http\Requests\Admin;

use App\Models\Postingan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostinganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'post_category_id' => ['required', 'integer', 'exists:post_categories,id'],
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string', 'max:20000'],
            'status' => ['required', Rule::in(array_keys(Postingan::STATUSES))],
            'gambar' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
