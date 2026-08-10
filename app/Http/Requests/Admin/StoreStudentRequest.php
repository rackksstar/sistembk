<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nisn' => ['required', 'string', 'max:20', 'unique:students,nisn'],
            'birth_date' => ['required', 'date', 'before:today'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'jenis_kelamin' => ['nullable', 'string', 'in:L,P'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'school' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', User::ROLE_SISWA)), Rule::unique('students', 'user_id')],
        ];
    }

    public function messages(): array
    {
        return [
            'nisn.unique' => 'NISN sudah digunakan siswa lain.',
            'birth_date.before' => 'Tanggal lahir harus valid dan sebelum hari ini.',
            'user_id.exists' => 'Akun login harus akun dengan role siswa.',
            'user_id.unique' => 'Akun login sudah terhubung ke siswa lain.',
        ];
    }
}
