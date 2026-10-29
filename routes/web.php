<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\FeeController;
use App\Http\Controllers\Admin\LookupController;
use App\Http\Controllers\Admin\MarkController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\AdmissionController as AdminAdmissionController;
use App\Http\Controllers\Admin\TestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

/* ---------------- Auth ---------------- */
Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* ---------------- Student portal ---------------- */
Route::prefix('portal')->middleware('student')->name('portal.')->group(function () {
    Route::get('/', [PortalController::class, 'show'])->name('show');
    Route::get('/id-card', [PortalController::class, 'idCard'])->name('id-card');
    Route::get('/sync', [PortalController::class, 'sync'])->name('sync');
});

/* ---------------- Admin panel ---------------- */
Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('students', [StudentController::class, 'index'])->name('students.index');
    Route::get('students/next-id', [StudentController::class, 'nextId'])->name('students.next-id');
    Route::post('students', [StudentController::class, 'store'])->name('students.store');
    Route::post('students/{id}/update', [StudentController::class, 'update'])->name('students.update');
    Route::post('students/{id}/delete', [StudentController::class, 'destroy'])->name('students.destroy');

    Route::get('lookup', [LookupController::class, 'index'])->name('lookup.index');

    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::post('messages/{id}/update', [MessageController::class, 'update'])->name('messages.update');
    Route::post('messages/{id}/delete', [MessageController::class, 'destroy'])->name('messages.destroy');

    Route::get('tests', [TestController::class, 'index'])->name('tests.index');
    Route::post('tests', [TestController::class, 'store'])->name('tests.store');
    Route::post('tests/{id}/update', [TestController::class, 'update'])->name('tests.update');
    Route::post('tests/{id}/delete', [TestController::class, 'destroy'])->name('tests.destroy');

    Route::get('marks', [MarkController::class, 'index'])->name('marks.index');
    Route::post('marks', [MarkController::class, 'save'])->name('marks.save');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'mark'])->name('attendance.mark');

    Route::get('scan', [AttendanceController::class, 'scanner'])->name('scan');
    Route::post('attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');

    Route::get('fees', [FeeController::class, 'index'])->name('fees.index');
    Route::post('fees/payment', [FeeController::class, 'addPayment'])->name('fees.payment');

    Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
    Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
    Route::post('exams/collect', [ExamController::class, 'collect'])->name('exams.collect');
    Route::post('exams/mark-paid', [ExamController::class, 'markPaid'])->name('exams.markPaid');

    Route::get('books', [BookController::class, 'index'])->name('books.index');
    Route::post('books/save', [BookController::class, 'save'])->name('books.save');
    Route::get('books/templates', [BookController::class, 'templates'])->name('books.templates');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('admissions', [AdminAdmissionController::class, 'index'])->name('admissions.index');
    Route::post('admissions', [AdminAdmissionController::class, 'store'])->name('admissions.store');
    Route::post('admissions/{id}/approve', [AdminAdmissionController::class, 'approve'])->name('admissions.approve');
    Route::post('admissions/{id}/reject', [AdminAdmissionController::class, 'reject'])->name('admissions.reject');

    Route::get('export/{type}', [ExportController::class, 'export'])->name('export');
});
