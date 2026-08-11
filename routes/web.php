<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminApprovalController;
use App\Http\Controllers\Admin\CareerInfoController;
use App\Http\Controllers\Admin\ConsultationController as AdminConsultationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuruBkController;
use App\Http\Controllers\Admin\GuruProfileChangeController;
use App\Http\Controllers\Admin\GuidanceClassController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MasterQuestionController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostinganController;
use App\Http\Controllers\Admin\RaporController as AdminRaporController;
use App\Http\Controllers\Admin\SekolahController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\GuruRegistrationController;
use App\Http\Controllers\Guru\ConsultationController as GuruConsultationController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\InstrumentQuestionController;
use App\Http\Controllers\Guru\InstrumentResultController;
use App\Http\Controllers\Guru\MonthlyJournalController;
use App\Http\Controllers\Guru\RplController;
use App\Http\Controllers\Guru\ServiceFeedbackController as GuruServiceFeedbackController;
use App\Http\Controllers\Guru\SociometryMapController;
use App\Http\Controllers\Guru\StudentController as GuruStudentController;
use App\Http\Controllers\Siswa\ConsultationController as SiswaConsultationController;
use App\Http\Controllers\Siswa\ConsultationRequestController;
use App\Http\Controllers\Siswa\CareerInfoController as SiswaCareerInfoController;
use App\Http\Controllers\Siswa\ChatbotController;
use App\Http\Controllers\Siswa\ClassJoinController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\InstrumentSubmissionController;
use App\Http\Controllers\Siswa\PostinganController as SiswaPostinganController;
use App\Http\Controllers\Siswa\ServiceFeedbackController;
use App\Http\Controllers\Siswa\SociometryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'requiresLogoutConfirmation' => Auth::check(),
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/register/guru-bk', [GuruRegistrationController::class, 'create'])->name('guru.register');
    Route::post('/register/guru-bk', [GuruRegistrationController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('guru.register.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/approvals', [AdminApprovalController::class, 'index'])->name('approvals.index');
        Route::patch('/approvals/{user}/approve', [AdminApprovalController::class, 'approve'])->name('approvals.approve');
        Route::patch('/approvals/{user}/reject', [AdminApprovalController::class, 'reject'])->name('approvals.reject');
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
        Route::resource('careers', CareerInfoController::class)->except(['create', 'show', 'edit']);
        Route::resource('students', StudentController::class)->except(['create', 'show', 'edit']);
        Route::resource('guidance-classes', GuidanceClassController::class)
            ->parameters(['guidance-classes' => 'guidanceClass'])
            ->except(['create', 'show', 'edit']);
        Route::post('/guidance-classes/{guidanceClass}/students', [GuidanceClassController::class, 'attachStudent'])->name('guidance-classes.students.attach');
        Route::delete('/guidance-classes/{guidanceClass}/students/{student}', [GuidanceClassController::class, 'detachStudent'])->name('guidance-classes.students.detach');
        Route::get('/consultations', [AdminConsultationController::class, 'index'])->name('consultations.index');
        Route::get('/rapor', [AdminRaporController::class, 'index'])->name('rapor.index');
        Route::get('/rapor/{rapor}', [AdminRaporController::class, 'show'])->name('rapor.show');
        Route::get('/rapor-cetak/{rapor}/pdf', [AdminRaporController::class, 'exportPdf'])->name('rapor.pdf');

        // Phase 2 — Data Master (tambahan di bawah route existing)
        Route::resource('sekolah', SekolahController::class)->except(['create', 'show', 'edit']);
        Route::resource('kelas', KelasController::class)->except(['create', 'show', 'edit']);
        Route::resource('guru-bk', GuruBkController::class)
            ->parameters(['guru-bk' => 'guruBk'])
            ->except(['create', 'show', 'edit']);
        Route::get('/perubahan-profil-guru', [GuruProfileChangeController::class, 'index'])->name('guru-profile-changes.index');
        Route::patch('/perubahan-profil-guru/{change}/dibaca', [GuruProfileChangeController::class, 'markReviewed'])->name('guru-profile-changes.reviewed');
        Route::resource('master-pertanyaan', MasterQuestionController::class)
            ->parameters(['master-pertanyaan' => 'masterPertanyaan'])
            ->except(['create', 'show', 'edit']);
        Route::resource('kategori-postingan', PostCategoryController::class)
            ->parameters(['kategori-postingan' => 'kategoriPostingan'])
            ->except(['create', 'show', 'edit']);
        Route::resource('postingan', PostinganController::class)
            ->except(['create', 'show', 'edit']);
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    Route::prefix('guru')->name('guru.')->middleware('role:guru')->group(function () {
        Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
        Route::post('/students/import', [GuruStudentController::class, 'import'])->name('students.import');
        Route::resource('students', GuruStudentController::class)->except(['create', 'show', 'edit']);
        Route::resource('instrument-questions', InstrumentQuestionController::class)
            ->parameters(['instrument-questions' => 'question'])
            ->except(['create', 'show', 'edit']);
        Route::get('/instrument-results', [InstrumentResultController::class, 'index'])->name('instrument-results.index');
        Route::get('/sociometry', [SociometryMapController::class, 'index'])->name('sociometry.index');
        Route::resource('rpls', RplController::class)->except(['create', 'show', 'edit']);
        Route::get('/rpls/{rpl}/print', [RplController::class, 'print'])->name('rpls.print');
        Route::resource('journals', MonthlyJournalController::class)->parameters(['journals' => 'journal'])->except(['create', 'show', 'edit']);
        Route::get('/journals/{journal}/print', [MonthlyJournalController::class, 'print'])->name('journals.print');
        Route::get('/feedback', [GuruServiceFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/consultations', [GuruConsultationController::class, 'index'])->name('consultations.index');
        Route::get('/consultations/events', [GuruConsultationController::class, 'events'])->name('consultations.events');
        Route::patch('/consultations/{consultation}/approve', [GuruConsultationController::class, 'approve'])->name('consultations.approve');
        Route::patch('/consultations/{consultation}/reject', [GuruConsultationController::class, 'reject'])->name('consultations.reject');
        Route::patch('/consultations/{consultation}/schedule', [GuruConsultationController::class, 'schedule'])->name('consultations.schedule');
        Route::patch('/consultations/{consultation}/report', [GuruConsultationController::class, 'report'])->name('consultations.report');
        Route::get('/consultations/{consultation}/print', [GuruConsultationController::class, 'print'])->name('consultations.print');

        // Phase 4–6 — Core (guru)
        Route::get('penilaian', [\App\Http\Controllers\Guru\PenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('angket', [\App\Http\Controllers\Guru\AngketController::class, 'index'])->name('angket.index');
        Route::get('angket/{student}/pdf', [\App\Http\Controllers\Guru\AngketController::class, 'exportPdf'])->name('angket.pdf');
        Route::get('angket/{student}', [\App\Http\Controllers\Guru\AngketController::class, 'show'])->name('angket.show');
        Route::get('rapor', [\App\Http\Controllers\Guru\RaporController::class, 'index'])->name('rapor.index');
        Route::get('rapor/{student}/edit', [\App\Http\Controllers\Guru\RaporController::class, 'edit'])->name('rapor.edit');
        Route::put('rapor/{student}', [\App\Http\Controllers\Guru\RaporController::class, 'update'])->name('rapor.update');
        Route::get('rapor-cetak/{rapor}/pdf', [\App\Http\Controllers\Guru\RaporController::class, 'exportPdf'])->name('rapor.pdf');
        Route::get('tryout', [\App\Http\Controllers\Guru\TryoutController::class, 'index'])->name('tryout.index');
        Route::get('tryout/buat', [\App\Http\Controllers\Guru\TryoutController::class, 'create'])->name('tryout.create');
        Route::post('tryout', [\App\Http\Controllers\Guru\TryoutController::class, 'store'])->name('tryout.store');
        Route::get('tryout/{tryout}/edit', [\App\Http\Controllers\Guru\TryoutController::class, 'edit'])->name('tryout.edit');
        Route::put('tryout/{tryout}', [\App\Http\Controllers\Guru\TryoutController::class, 'update'])->name('tryout.update');
        Route::delete('tryout/{tryout}', [\App\Http\Controllers\Guru\TryoutController::class, 'destroy'])->name('tryout.destroy');
        Route::get('tryout/{tryout}', [\App\Http\Controllers\Guru\TryoutController::class, 'show'])->name('tryout.show');
    });

    Route::prefix('siswa')->name('siswa.')->middleware('role:siswa')->group(function () {
        Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
        Route::get('/instruments', [InstrumentSubmissionController::class, 'index'])->name('instruments.index');
        Route::post('/instruments', [InstrumentSubmissionController::class, 'store'])->name('instruments.store');
        Route::get('/sociometry', [SociometryController::class, 'index'])->name('sociometry.index');
        Route::post('/sociometry', [SociometryController::class, 'store'])->name('sociometry.store');
        Route::get('/consultations', [SiswaConsultationController::class, 'index'])->name('consultations.index');
        Route::post('/consultations', [SiswaConsultationController::class, 'store'])->name('consultations.store');
        Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
        Route::post('/chatbot', [ChatbotController::class, 'store'])->name('chatbot.store');
        Route::post('/consultation-requests', [ConsultationRequestController::class, 'store'])->name('consultation-requests.store');
        Route::post('/classes/join', [ClassJoinController::class, 'store'])->name('classes.join');
        Route::get('/careers', [SiswaCareerInfoController::class, 'index'])->name('careers.index');
        Route::get('/postingan', [SiswaPostinganController::class, 'index'])->name('postingan.index');
        Route::get('/postingan/{postingan}', [SiswaPostinganController::class, 'show'])->name('postingan.show');
        Route::get('/feedback', [ServiceFeedbackController::class, 'create'])->name('feedback.create');
        Route::post('/feedback', [ServiceFeedbackController::class, 'store'])->name('feedback.store');

        // Phase 4–6 — Core (siswa)
        Route::get('penilaian', [\App\Http\Controllers\Siswa\PenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('penilaian/buat', [\App\Http\Controllers\Siswa\PenilaianController::class, 'create'])->name('penilaian.create');
        Route::post('penilaian', [\App\Http\Controllers\Siswa\PenilaianController::class, 'store'])->name('penilaian.store');
        Route::get('angket', [\App\Http\Controllers\Siswa\AngketController::class, 'index'])->name('angket.index');
        Route::get('angket/isi', [\App\Http\Controllers\Siswa\AngketController::class, 'show'])->name('angket.show');
        Route::post('angket', [\App\Http\Controllers\Siswa\AngketController::class, 'store'])->name('angket.store');
        Route::get('tryout', [\App\Http\Controllers\Siswa\TryoutController::class, 'index'])->name('tryout.index');
        Route::get('tryout/{tryout}', [\App\Http\Controllers\Siswa\TryoutController::class, 'show'])->name('tryout.show');
        Route::post('tryout/{tryout}', [\App\Http\Controllers\Siswa\TryoutController::class, 'store'])->name('tryout.store');
    });

    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
