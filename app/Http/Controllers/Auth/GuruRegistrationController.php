<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterGuruRequest;
use App\Models\GuruBk;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class GuruRegistrationController extends Controller
{
    public function create(): View
    {
        $sekolahs = Sekolah::query()
            ->where('is_active', true)
            ->where('is_mou', true)
            ->orderBy('nama')
            ->get();

        return view('auth.register-guru', compact('sekolahs'));
    }

    public function store(RegisterGuruRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $sekolah = Sekolah::findOrFail($data['sekolah_id']);
        $username = $data['no_hp'];

        $user = User::create([
            'name' => $data['name'],
            'username' => $username,
            'password' => Hash::make($data['password']),
            'school' => $sekolah->nama,
            'role' => User::ROLE_GURU,
            'status' => User::STATUS_PENDING,
        ]);

        GuruBk::create([
            'user_id' => $user->id,
            'sekolah_id' => $sekolah->id,
            'no_hp' => $data['no_hp'],
            'nip' => $data['nip'],
            'jabatan' => 'Guru BK',
        ]);

        return redirect()->route('login')
            ->with('status', 'Pendaftaran Guru BK berhasil dikirim dan menunggu persetujuan admin.');
    }
}
