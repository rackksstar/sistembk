<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\GuidanceClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClassJoinController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40'],
        ], [
            'code.required' => 'Silakan isi kode kelas.',
        ]);

        $student = $request->user()->studentProfile;

        if (! $student) {
            return back()->with('error', 'Profil siswa belum tersedia. Hubungi admin untuk melengkapi data siswa.');
        }

        $class = GuidanceClass::where('code', strtoupper(trim($data['code'])))->first();

        if (! $class) {
            return back()->withErrors(['code' => 'Kode kelas tidak ditemukan.']);
        }

        $student->guidanceClasses()->syncWithoutDetaching([$class->id]);

        return back()->with('success', 'Berhasil masuk ke kelas bimbingan.');
    }
}
