<?php

namespace App\Http\Requests\Admin;

use App\Models\MasterQuestion;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMasterQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'kategori' => ['required', 'string', 'in:'.implode(',', MasterQuestion::KATEGORI)],
            'teks_pertanyaan' => ['required', 'string', 'max:2000'],
            'tipe_input' => ['required', 'string', 'in:'.implode(',', MasterQuestion::TIPE_INPUT)],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

