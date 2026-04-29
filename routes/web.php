<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminApprovalController;
use App\Http\Controllers\Admin\CareerInfoController;
use App\Http\Controllers\Admin\ConsultationController as AdminConsultationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GuidanceClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\GuruRegistrationController;
use App\Http\Controllers\Guru\ConsultationController as GuruConsultationController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Siswa\ConsultationRequestController;
use App\Http\Controllers\Siswa\CareerInfoController as SiswaCareerInfoController;
use App\Http\Controllers\Siswa\ClassJoinController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'requiresLogoutConfirmation' => Auth::check(),
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/register/guru-bk', [GuruRegistrationController::class, 'create'])->name('guru.register');
    Route::post('/register/guru-bk', [GuruRegistrationController::class, 'store'])->name('guru.register.store');
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
    });

    Route::prefix('guru')->name('guru.')->middleware('role:guru')->group(function () {
        Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
        Route::get('/consultations', [GuruConsultationController::class, 'index'])->name('consultations.index');
        Route::patch('/consultations/{consultation}/approve', [GuruConsultationController::class, 'approve'])->name('consultations.approve');
        Route::patch('/consultations/{consultation}/schedule', [GuruConsultationController::class, 'schedule'])->name('consultations.schedule');
        Route::patch('/consultations/{consultation}/report', [GuruConsultationController::class, 'report'])->name('consultations.report');
        Route::get('/consultations/{consultation}/print', [GuruConsultationController::class, 'print'])->name('consultations.print');
    });

    Route::prefix('siswa')->name('siswa.')->middleware('role:siswa')->group(function () {
        Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
        Route::post('/consultation-requests', [ConsultationRequestController::class, 'store'])->name('consultation-requests.store');
        Route::post('/classes/join', [ClassJoinController::class, 'store'])->name('classes.join');
        Route::get('/careers', [SiswaCareerInfoController::class, 'index'])->name('careers.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
