<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['nullable', 'in:'.User::ROLE_GURU.','.User::ROLE_SISWA],
            'nisn' => ['required_if:role,'.User::ROLE_SISWA, 'nullable', 'string', 'max:20'],
            'birth_date' => ['required_if:role,'.User::ROLE_SISWA, 'nullable', 'date', 'before:today'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'nisn.required_if' => 'NISN wajib diisi untuk pendaftaran siswa.',
            'birth_date.required_if' => 'Tanggal lahir wajib diisi untuk pendaftaran siswa.',
            'birth_date.before' => 'Tanggal lahir harus valid dan sebelum hari ini.',
        ]);

        $role = $request->role ?? User::ROLE_SISWA;
        $student = null;

        if ($role === User::ROLE_SISWA && $request->filled('nisn')) {
            $student = Student::where('nisn', $request->nisn)->first();

            if (! $student) {
                throw ValidationException::withMessages([
                    'nisn' => 'NISN tidak ditemukan pada data siswa. Hubungi admin sekolah.',
                ]);
            }

            if ($student->birth_date?->toDateString() !== $request->birth_date) {
                throw ValidationException::withMessages([
                    'birth_date' => 'Tanggal lahir tidak cocok dengan data siswa.',
                ]);
            }

            if ($student->user_id) {
                throw ValidationException::withMessages([
                    'nisn' => 'NISN ini sudah terhubung dengan akun siswa.',
                ]);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'status' => $role === User::ROLE_GURU ? User::STATUS_PENDING : User::STATUS_APPROVED,
        ]);

        event(new Registered($user));

        if ($student) {
            $student->update([
                'user_id' => $user->id,
                'name' => $request->name,
            ]);
        }

        if ($user->role === User::ROLE_GURU) {
            return redirect(route('login', absolute: false))
                ->with('status', 'Pendaftaran Guru BK berhasil dikirim dan menunggu persetujuan admin.');
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
