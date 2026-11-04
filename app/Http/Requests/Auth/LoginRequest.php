<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nisn' => ['required_if:selected_role,siswa', 'nullable', 'string', 'max:20'],
            'birth_date' => ['required_if:selected_role,siswa', 'nullable', 'date', 'before:today'],
            'login_id' => ['required_if:selected_role,guru', 'nullable', 'string', 'max:255'],
            'email' => ['required_unless:selected_role,siswa,guru', 'nullable', 'string', 'max:255'],
            'password' => ['required_unless:selected_role,siswa', 'nullable', 'string'],
            'selected_role' => ['nullable', 'string', 'in:admin,guru,siswa'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $isGuruLogin = $this->string('selected_role')->toString() === 'guru';
        $identifier = trim($isGuruLogin
            ? $this->string('login_id')->toString()
            : $this->string('email')->toString());
        $normalizedIdentifier = preg_replace('/\D+/', '', $identifier);
        $identifiers = collect([$identifier, $normalizedIdentifier])
            ->filter()
            ->unique()
            ->values()
            ->all();
        $userQuery = User::query();

        if ($isGuruLogin) {
            $userQuery
                ->where('role', User::ROLE_GURU)
                ->where(function ($query) use ($identifiers) {
                    $query->whereIn('username', $identifiers)
                        ->orWhereHas('guruBkProfile', function ($profileQuery) use ($identifiers) {
                            $profileQuery->whereIn('no_hp', $identifiers)
                                ->orWhereIn('nip', $identifiers);
                        });
                });
        } else {
            $userQuery->where('email', $identifier);
        }

        $user = $userQuery->first();

        if (! $user || ! Hash::check($this->string('password')->toString(), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $isGuruLogin ? 'login_id' : 'email' => trans('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $errorKey = match ($this->string('selected_role')->toString()) {
            'siswa' => 'nisn',
            'guru' => 'login_id',
            default => 'email',
        };

        throw ValidationException::withMessages([
            $errorKey => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $identifier = match ($this->string('selected_role')->toString()) {
            'siswa' => $this->string('nisn')->toString(),
            'guru' => $this->string('login_id')->toString(),
            default => $this->string('email')->toString(),
        };

        return Str::transliterate(Str::lower($identifier).'|'.$this->ip());
    }
}
