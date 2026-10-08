<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SociometryResponse;
use App\Models\SosiometryInstrument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Modul Sosiometri (sisi siswa): siswa memilih 1 teman dekat dan 1 teman
 * belajar beserta alasannya. Mengisi ulang akan MENGGANTI pilihan sebelumnya
 * (lihat store(): baris lama dihapus dulu sebelum baris baru dibuat).
 *
 * Guru bisa menonaktifkan instrumen per kelas lewat menu "Kelola Sosiometri"
 * (SosiometryInstrumentController). Saat kelas siswa dinonaktifkan, form
 * disembunyikan dan penyimpanan ditolak.
 */
class SociometryController extends Controller
{
    // Form isi sosiometri + riwayat pilihan siswa yang sedang login.
    public function index(): View
    {
        $this->assertInstrumentActive();

        $students = User::query()
            ->where('role', User::ROLE_SISWA)
            ->where('id', '!=', auth()->id())
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get(['id', 'name']);

        $responses = SociometryResponse::query()
            ->where('student_id', auth()->id())
            ->with('chosenStudent:id,name')
            ->latest()
            ->get();

        return view('siswa.sociometry.index', compact('students', 'responses'));
    }

    // Menyimpan pilihan sosiometri siswa (1 teman dekat + 1 teman belajar).
    public function store(Request $request): RedirectResponse
    {
        $this->assertInstrumentActive();

        $validated = $request->validate([
            'close_friend_id' => ['required', 'integer', 'exists:users,id', 'different:study_friend_id'],
            'study_friend_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [
            'close_friend_id.different' => 'Teman dekat dan teman belajar harus berbeda.',
        ]);

        foreach ([$validated['close_friend_id'], $validated['study_friend_id']] as $studentId) {
            abort_if((int) $studentId === auth()->id(), 422, 'Tidak boleh memilih diri sendiri.');
        }

        DB::transaction(function () use ($validated) {
            SociometryResponse::where('student_id', auth()->id())->delete();

            SociometryResponse::create([
                'student_id' => auth()->id(),
                'chosen_student_id' => $validated['close_friend_id'],
                'relation_type' => SociometryResponse::TYPE_CLOSE_FRIEND,
                'reason' => $validated['reason'] ?? null,
                'submitted_at' => now(),
            ]);

            SociometryResponse::create([
                'student_id' => auth()->id(),
                'chosen_student_id' => $validated['study_friend_id'],
                'relation_type' => SociometryResponse::TYPE_STUDY_FRIEND,
                'reason' => $validated['reason'] ?? null,
                'submitted_at' => now(),
            ]);
        });

        return back()->with('success', 'Pilihan sosiometri berhasil disimpan.');
    }

    /**
     * Tolak aksi bila instrumen sosiometri kelas siswa dinonaktifkan guru.
     * Kelas tanpa baris pengaturan dianggap aktif (default).
     */
    private function assertInstrumentActive(): void
    {
        $kelasId = auth()->user()?->studentProfile?->kelas_id;

        if (! $kelasId) {
            return;
        }

        $instrument = SosiometryInstrument::query()->where('kelas_id', $kelasId)->first();

        if ($instrument && ! $instrument->is_active) {
            abort(403, 'Instrumen sosiometri untuk kelas Anda sedang dinonaktifkan. Silakan hubungi guru BK.');
        }
    }
}
