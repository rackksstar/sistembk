<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class RegisterGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sekolah_id' => [
                'required',
                Rule::exists('sekolahs', 'id')->where(fn ($query) => $query->where('is_active', true)->where('is_mou', true)),
            ],
            'no_hp' => ['required', 'string', 'max:30', 'unique:guru_bks,no_hp', 'unique:users,username'],
            'nip' => ['required', 'string', 'max:40', 'unique:guru_bks,nip'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
