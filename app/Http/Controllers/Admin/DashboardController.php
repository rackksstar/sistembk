<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerInfo;
use App\Models\ConsultationRequest;
use App\Models\GuidanceClass;
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
                'title' => 'Guru pending',
                'value' => User::where('role', User::ROLE_GURU)->where('status', User::STATUS_PENDING)->count(),
                'description' => 'Pendaftaran Guru BK yang menunggu approval.',
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
            ['title' => 'Manajemen Pengguna', 'description' => 'Atur akun admin, Guru BK, dan siswa.', 'href' => route('admin.users.index'), 'count' => User::count()],
            ['title' => 'Data Siswa', 'description' => 'Kelola NISN, tanggal lahir, dan profil siswa.', 'href' => route('admin.students.index'), 'count' => Student::count()],
            ['title' => 'Kelas Bimbingan', 'description' => 'Buat kelas dan tambahkan siswa ke kelas.', 'href' => route('admin.guidance-classes.index'), 'count' => GuidanceClass::count()],
            ['title' => 'Informasi Karier', 'description' => 'Kelola konten karier read-only untuk siswa.', 'href' => route('admin.careers.index'), 'count' => CareerInfo::count()],
            ['title' => 'Konseling & Laporan', 'description' => 'Pantau pengajuan, jadwal, hasil, dan evaluasi.', 'href' => route('admin.consultations.index'), 'count' => ConsultationRequest::count()],
        ];

        return view('admin.dashboard', compact('metrics', 'recentRequests', 'roleSummary', 'modules'));
    }
}
