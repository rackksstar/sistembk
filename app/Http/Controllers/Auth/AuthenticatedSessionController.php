<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $selectedRole = in_array($request->query('role'), User::ROLES, true)
            ? $request->query('role')
            : null;

        return view('auth.login', compact('selectedRole'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        if ($request->input('selected_role') === User::ROLE_SISWA) {
            return $this->storeStudentSession($request);
        }

        $request->authenticate();

        $request->session()->regenerate();

        if ($request->filled('selected_role') && $request->user()->role !== $request->input('selected_role')) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak sesuai dengan role yang dipilih. Silakan pilih role yang benar di landing page.',
            ]);
        }

        if (! $request->user()->isApproved()) {
            $message = $request->user()->status === 'pending'
                ? 'Akun Anda masih menunggu persetujuan admin.'
                : 'Pendaftaran akun Anda ditolak. Silakan hubungi admin sekolah.';

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => $message]);
        }

        return redirect()->route($request->user()->dashboardRoute());
    }

    private function storeStudentSession(LoginRequest $request): RedirectResponse
    {
        $student = Student::where('nisn', $request->input('nisn'))->first();

        if (! $student) {
            throw ValidationException::withMessages([
                'nisn' => 'NISN tidak ditemukan pada data siswa. Hubungi admin atau Guru BK.',
            ]);
        }

        if ($student->birth_date?->toDateString() !== $request->input('birth_date')) {
            throw ValidationException::withMessages([
                'birth_date' => 'Tanggal lahir tidak cocok dengan data siswa.',
            ]);
        }

        $user = $student->user;

        if (! $user) {
            throw ValidationException::withMessages([
                'nisn' => 'Akun siswa belum aktif. Hubungi admin untuk menghubungkan data siswa dengan akun login.',
            ]);
        }

        if ($user->role !== User::ROLE_SISWA) {
            throw ValidationException::withMessages([
                'nisn' => 'Data siswa ini terhubung dengan akun yang tidak valid. Hubungi admin.',
            ]);
        }

        if (! $user->isApproved()) {
            throw ValidationException::withMessages([
                'nisn' => 'Akun siswa ini belum aktif. Silakan hubungi admin.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->route($user->dashboardRoute());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
