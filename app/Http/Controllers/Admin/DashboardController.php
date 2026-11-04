<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerInfo;
use App\Models\ConsultationRequest;
use App\Models\GuidanceClass;
use App\Models\GuruBk;
use App\Models\GuruProfileChange;
use App\Models\Kelas;
use App\Models\MasterQuestion;
use App\Models\Postingan;
use App\Models\RaporBk;
use App\Models\Sekolah;
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

        $recentRequests = ConsultationRequest::with([
            'student:id,name',
            'student.studentProfile:id,user_id,kelas_id',
            'student.studentProfile.kelas:id,nama',
            'counselor:id,name',
        ])
            ->latest()
            ->take(5)
            ->get();

        $roleSummary = [
            ['label' => 'Admin', 'count' => User::where('role', User::ROLE_ADMIN)->count(), 'color' => 'bg-slate-700 dark:bg-slate-300'],
            ['label' => 'Guru BK', 'count' => User::where('role', User::ROLE_GURU)->count(), 'color' => 'bg-blue-600'],
            ['label' => 'Siswa', 'count' => User::where('role', User::ROLE_SISWA)->count(), 'color' => 'bg-sky-500'],
        ];

        $modules = [
            ['title' => 'Approval Guru BK', 'description' => 'Setujui atau tolak pendaftaran Guru BK.', 'href' => route('admin.approvals.index'), 'count' => User::where('role', User::ROLE_GURU)->where('status', User::STATUS_PENDING)->count()],
            ['title' => 'Sekolah MOU', 'description' => 'Input dan kelola sekolah yang sudah MOU dengan PCR.', 'href' => route('admin.sekolah.index'), 'count' => Sekolah::where('is_mou', true)->count()],
            ['title' => 'Guru BK', 'description' => 'Kelola data Guru BK per sekolah.', 'href' => route('admin.guru-bk.index'), 'count' => User::where('role', User::ROLE_GURU)->count()],
            ['title' => 'Perubahan Profil Guru', 'description' => 'Lihat perubahan no HP, NIP, dan nama Guru BK.', 'href' => route('admin.guru-profile-changes.index'), 'count' => GuruProfileChange::whereNull('reviewed_at')->count()],
            ['title' => 'Manajemen Pengguna', 'description' => 'Atur akun admin, Guru BK, dan siswa.', 'href' => route('admin.users.index'), 'count' => User::count()],
            ['title' => 'Data Siswa', 'description' => 'Kelola NISN, tanggal lahir, dan profil siswa.', 'href' => route('admin.students.index'), 'count' => Student::count()],
            ['title' => 'Kelas Bimbingan', 'description' => 'Buat kelas dan tambahkan siswa ke kelas.', 'href' => route('admin.guidance-classes.index'), 'count' => GuidanceClass::count()],
            ['title' => 'Master Pertanyaan', 'description' => 'Kelola soal angket dan tryout.', 'href' => route('admin.master-pertanyaan.index'), 'count' => MasterQuestion::count()],
            ['title' => 'Kategori Minat RIASEC', 'description' => 'Master kategori minat untuk asesmen kuliah.', 'href' => route('admin.interest-categories.index'), 'count' => \App\Models\InterestCategory::count()],
            ['title' => 'Program Studi PCR', 'description' => 'Kelola & verifikasi prodi rekomendasi siswa SMA.', 'href' => route('admin.program-studi.index'), 'count' => \App\Models\ProgramStudi::count()],
            ['title' => 'Bidang Karier', 'description' => 'Kelola bidang karier rekomendasi siswa SMK.', 'href' => route('admin.bidang-karier.index'), 'count' => \App\Models\CareerField::count()],
            ['title' => 'Informasi Karier', 'description' => 'Kelola konten karier read-only untuk siswa.', 'href' => route('admin.careers.index'), 'count' => CareerInfo::count()],
            ['title' => 'Konseling & Laporan', 'description' => 'Pantau pengajuan, jadwal, hasil, dan evaluasi.', 'href' => route('admin.consultations.index'), 'count' => ConsultationRequest::count()],
            ['title' => 'Log Aktivitas', 'description' => 'Audit jejak aktivitas pengguna sistem.', 'href' => route('admin.activity-logs.index'), 'count' => \App\Models\ActivityLog::count()],
        ];

        $postinganTerbaru = Postingan::query()
            ->with('kategori')
            ->latest()
            ->take(3)
            ->get();

        $coreSummary = [
            ['label' => 'Rapor BK', 'value' => RaporBk::count(), 'href' => route('admin.rapor.index')],
            ['label' => 'Postingan', 'value' => Postingan::count(), 'href' => route('admin.postingan.index')],
            ['label' => 'Soal Tryout', 'value' => MasterQuestion::where('kategori', MasterQuestion::KATEGORI_TRYOUT)->count(), 'href' => route('admin.master-pertanyaan.index', ['kategori' => 'tryout'])],
            ['label' => 'Kelas', 'value' => Kelas::count(), 'href' => route('admin.kelas.index')],
        ];

        $sekolahStats = [
            'total' => Sekolah::count(),
            'aktif' => Sekolah::where('is_active', true)->count(),
            'nonaktif' => Sekolah::where('is_active', false)->count(),
            'mou' => Sekolah::where('is_mou', true)->count(),
            'paket' => Sekolah::whereNotNull('paket_aktif')->where('paket_aktif', '!=', '')->count(),
        ];

        $sekolahTerbaru = Sekolah::query()
            ->latest()
            ->take(5)
            ->get(['id', 'nama', 'npsn', 'is_active', 'is_mou', 'paket_aktif', 'tanggal_aktivasi', 'created_at']);

        // Penggunaan sistem per sekolah (siap uji min. 3 sekolah — catatan dosen).
        $penggunaanPerSekolah = Sekolah::query()
            ->orderBy('nama')
            ->get(['id', 'nama', 'is_active', 'is_mou'])
            ->map(function (Sekolah $sekolah) {
                $siswaCount = Student::query()
                    ->whereHas('kelas', fn ($q) => $q->where('sekolah_id', $sekolah->id))
                    ->count();

                $guruCount = GuruBk::query()
                    ->where('sekolah_id', $sekolah->id)
                    ->count();

                $konselingCount = ConsultationRequest::query()
                    ->whereHas(
                        'student.studentProfile.kelas',
                        fn ($q) => $q->where('sekolah_id', $sekolah->id)
                    )
                    ->count();

                $prodiCount = ConsultationRequest::query()
                    ->where('case_category', ConsultationRequest::CASE_PRODI_KULIAH)
                    ->whereHas(
                        'student.studentProfile.kelas',
                        fn ($q) => $q->where('sekolah_id', $sekolah->id)
                    )
                    ->count();

                return [
                    'id' => $sekolah->id,
                    'nama' => $sekolah->nama,
                    'is_active' => $sekolah->is_active,
                    'is_mou' => $sekolah->is_mou,
                    'siswa' => $siswaCount,
                    'guru' => $guruCount,
                    'konseling' => $konselingCount,
                    'konsultasi_prodi' => $prodiCount,
                ];
            });

        return view('admin.dashboard', compact(
            'metrics', 'recentRequests', 'roleSummary', 'modules',
            'postinganTerbaru', 'coreSummary', 'sekolahStats', 'sekolahTerbaru',
            'penggunaanPerSekolah'
        ));
    }
}
