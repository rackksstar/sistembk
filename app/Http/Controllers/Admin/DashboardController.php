<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerInfo;
use App\Models\ConsultationRequest;
use App\Models\GuidanceClass;
use App\Models\GuruProfileChange;
use App\Models\Postingan;
use App\Models\RaporBk;
use App\Models\Sekolah;
use App\Models\TryOut;
use App\Models\Student;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $metrics = [
            [
                'title' => 'Total pengguna',
                'value' => User::count(),
                'description' => 'Admin, Guru BK, dan Siswa aktif.',
                'color' => 'from-blue-600 to-sky-400',
            ],
            [
                'title' => 'Guru BK',
                'value' => User::where('role', User::ROLE_GURU)->count(),
                'description' => 'Konselor yang tersedia di sistem.',
                'color' => 'from-emerald-500 to-teal-400',
            ],
            [
                'title' => 'Permintaan menunggu',
                'value' => ConsultationRequest::where('status', ConsultationRequest::STATUS_MENUNGGU)->count(),
                'description' => 'Butuh tindak lanjut dari Guru BK.',
                'color' => 'from-amber-500 to-orange-400',
            ],
            [
                'title' => 'Update profil guru',
                'value' => GuruProfileChange::whereNull('reviewed_at')->count(),
                'description' => 'Perubahan profil Guru BK yang belum dibaca.',
                'color' => 'from-violet-500 to-fuchsia-400',
            ],
        ];

        $recentRequests = ConsultationRequest::with(['student', 'counselor'])
            ->latest()
            ->take(5)
            ->get();

        $roleSummary = [
            ['label' => 'Admin', 'count' => User::where('role', User::ROLE_ADMIN)->count(), 'color' => 'bg-slate-900'],
            ['label' => 'Guru BK', 'count' => User::where('role', User::ROLE_GURU)->count(), 'color' => 'bg-blue-600'],
            ['label' => 'Siswa', 'count' => User::where('role', User::ROLE_SISWA)->count(), 'color' => 'bg-sky-500'],
        ];

        $modules = [
            ['title' => 'Approval Guru BK', 'description' => 'Setujui atau tolak pendaftaran Guru BK.', 'href' => route('admin.approvals.index'), 'count' => User::where('role', User::ROLE_GURU)->where('status', User::STATUS_PENDING)->count()],
            ['title' => 'Sekolah MOU', 'description' => 'Input dan kelola sekolah yang sudah MOU dengan PCR.', 'href' => route('admin.sekolah.index'), 'count' => Sekolah::where('is_mou', true)->count()],
            ['title' => 'Perubahan Profil Guru', 'description' => 'Lihat perubahan no HP, NIP, dan nama Guru BK.', 'href' => route('admin.guru-profile-changes.index'), 'count' => GuruProfileChange::whereNull('reviewed_at')->count()],
            ['title' => 'Manajemen Pengguna', 'description' => 'Atur akun admin, Guru BK, dan siswa.', 'href' => route('admin.users.index'), 'count' => User::count()],
            ['title' => 'Data Siswa', 'description' => 'Kelola NISN, tanggal lahir, dan profil siswa.', 'href' => route('admin.students.index'), 'count' => Student::count()],
            ['title' => 'Kelas Bimbingan', 'description' => 'Buat kelas dan tambahkan siswa ke kelas.', 'href' => route('admin.guidance-classes.index'), 'count' => GuidanceClass::count()],
            ['title' => 'Informasi Karier', 'description' => 'Kelola konten karier read-only untuk siswa.', 'href' => route('admin.careers.index'), 'count' => CareerInfo::count()],
            ['title' => 'Konseling & Laporan', 'description' => 'Pantau pengajuan, jadwal, hasil, dan evaluasi.', 'href' => route('admin.consultations.index'), 'count' => ConsultationRequest::count()],
        ];

        $postinganTerbaru = Postingan::query()
            ->with('kategori')
            ->latest()
            ->take(3)
            ->get();

        $coreSummary = [
            ['label' => 'Rapor BK', 'value' => RaporBk::count(), 'href' => route('admin.rapor.index')],
            ['label' => 'Postingan', 'value' => Postingan::count(), 'href' => route('admin.postingan.index')],
            ['label' => 'Tryout', 'value' => TryOut::count(), 'href' => route('admin.master-pertanyaan.index', ['kategori' => 'tryout'])],
        ];

        return view('admin.dashboard', compact('metrics', 'recentRequests', 'roleSummary', 'modules', 'postinganTerbaru', 'coreSummary'));
    }
}
