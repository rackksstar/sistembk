<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,guru,siswa'],
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $throttleKey = 'api-login|'.Str::lower($validated['login']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        try {
            $user = $validated['role'] === User::ROLE_SISWA
                ? $this->authenticateStudent($validated['login'], $validated['password'])
                : $this->authenticateStaff($validated['role'], $validated['login'], $validated['password']);
        } catch (ValidationException $exception) {
            RateLimiter::hit($throttleKey);

            throw $exception;
        }

        if ($user->status !== User::STATUS_APPROVED) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'login' => ['Akun belum disetujui.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        $token = $user->createToken($validated['device_name'] ?? 'api-token')->plainTextToken;

        ActivityLogger::log('api.login', $user);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    private function authenticateStaff(string $role, string $login, string $password): User
    {
        $field = $role === User::ROLE_GURU ? 'username' : 'email';

        $user = User::query()
            ->where($field, $login)
            ->where('role', $role)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Kredensial tidak valid.'],
            ]);
        }

        return $user;
    }

    private function authenticateStudent(string $nisn, string $birthDate): User
    {
        $student = Student::query()->where('nisn', $nisn)->first();

        if (! $student || $student->birth_date?->toDateString() !== $this->normalizeBirthDate($birthDate)) {
            throw ValidationException::withMessages([
                'login' => ['NISN atau tanggal lahir tidak valid.'],
            ]);
        }

        $user = $student->user;

        if (! $user || $user->role !== User::ROLE_SISWA) {
            throw ValidationException::withMessages([
                'login' => ['Akun siswa belum terhubung. Hubungi admin.'],
            ]);
        }

        return $user;
    }

    private function normalizeBirthDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            return $date;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'role' => $user->role,
            'status' => $user->status,
        ];
    }
}
