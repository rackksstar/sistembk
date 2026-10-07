<?php

namespace App\Http\Requests\Guru;

use App\Http\Requests\Guru\Concerns\ValidatesInstrumentQuestion;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreInstrumentQuestionRequest extends FormRequest
{
    use ValidatesInstrumentQuestion;

    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_GURU;
    }
}
