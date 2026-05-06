<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('post_categories', 'name')->ignore($this->route('kategoriPostingan'))],
        ];
    }
}

