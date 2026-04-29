<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterGuruRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class GuruRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-guru');
    }

    public function store(RegisterGuruRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'school' => $request->validated('school'),
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_PENDING,
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        return redirect()->route('login')
            ->with('status', 'Pendaftaran Guru BK berhasil dikirim dan menunggu persetujuan admin.');
    }
}
