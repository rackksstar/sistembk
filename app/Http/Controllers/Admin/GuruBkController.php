<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGuruBkRequest;
use App\Http\Requests\Admin\UpdateGuruBkRequest;
use App\Models\GuruBk;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

class GuruBkController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $sekolahId = $request->string('sekolah_id')->toString();
        $status = $request->string('status')->toString();

        $guruBks = GuruBk::query()
            ->with(['user', 'sekolah'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"))
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            })
            ->when($sekolahId, fn ($q) => $q->where('sekolah_id', $sekolahId))
            ->when($status, fn ($q) => $q->whereHas('user', fn ($uq) => $uq->where('status', $status)))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $sekolahs = Sekolah::orderBy('nama')->get();

        return view('admin.guru-bk.index', [
            'guruBks' => $guruBks,
            'sekolahs' => $sekolahs,
            'statuses' => User::STATUSES,
            'search' => $search,
            'sekolahId' => $sekolahId,
            'status' => $status,
        ]);
    }

    public function store(StoreGuruBkRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $sekolah = Sekolah::find($data['sekolah_id']);
        $username = $data['no_hp'] ?: ($data['nip'] ?? null);

        try {
            DB::transaction(function () use ($data, $sekolah, $username) {
                $user = User::create([
                    'name' => $data['name'],
                    'username' => $username,
                    'password' => Hash::make($data['password']),
                    'school' => $sekolah?->nama,
                    'role' => User::ROLE_GURU,
                    'status' => $data['status'],
                ]);

                GuruBk::create([
                    'user_id' => $user->id,
                    'sekolah_id' => $data['sekolah_id'],
                    'no_hp' => $data['no_hp'],
                    'nip' => $data['nip'] ?? null,
                    'jabatan' => $data['jabatan'] ?? null,
                    'bidang_studi' => $data['bidang_studi'] ?? null,
                ]);
            });
        } catch (QueryException $exception) {
            return back()->withErrors([
                'no_hp' => 'No. HP atau NIP sudah digunakan Guru BK lain.',
            ])->withInput();
        }

        return back()->with('success', 'Data Guru BK berhasil dibuat.');
    }

    public function update(UpdateGuruBkRequest $request, GuruBk $guruBk): RedirectResponse
    {
        $data = $request->validated();
        $sekolah = Sekolah::find($data['sekolah_id']);
        $username = $data['no_hp'] ?: ($data['nip'] ?? null);

        $payloadUser = [
            'name' => $data['name'],
            'username' => $username,
            'school' => $sekolah?->nama,
            'status' => $data['status'],
        ];

        if (! empty($data['password'])) {
            $payloadUser['password'] = Hash::make($data['password']);
        }

        $guruBk->user->update($payloadUser);

        $guruBk->update([
            'sekolah_id' => $data['sekolah_id'],
            'no_hp' => $data['no_hp'],
            'nip' => $data['nip'] ?? null,
            'jabatan' => $data['jabatan'] ?? null,
            'bidang_studi' => $data['bidang_studi'] ?? null,
        ]);

        return back()->with('success', 'Data Guru BK berhasil diperbarui.');
    }

    public function destroy(GuruBk $guruBk): RedirectResponse
    {
        $user = $guruBk->user;

        $guruBk->delete();
        $user?->delete();

        return back()->with('success', 'Data Guru BK berhasil dihapus.');
    }
}
