<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\CorrectionRequestController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\StampCorrectionRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;

Fortify::registerView(fn () => view('auth.register'));
Fortify::loginView(fn () => view('auth.login'));

Route::get('/', function () {
    return auth()->check() ? redirect('/attendance') : redirect('/login');
});

Route::get('/email/verify', fn () => view('auth.verify-email'))
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/attendance');
})
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証メールを再送しました！');
})
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->name('login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::post('/attendance', [AttendanceController::class, 'store'])
        ->name('attendance.store');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    Route::get('/attendance/detail/date/{date}', [AttendanceController::class, 'showByDate'])
        ->name('attendance.showByDate');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])
        ->name('attendance.show');

    Route::post('/attendance/detail/{id}/request', [AttendanceController::class, 'requestChange'])
        ->name('attendance.request');

    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AdminLoginController::class, 'login'])
        ->name('login.submit');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
            ->name('attendance.list');

        Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])
            ->name('attendance.show');

        Route::post('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
            ->name('attendance.update');

        Route::get('/staff/list', [StaffController::class, 'index'])
            ->name('staff.list');

        Route::get('/attendance/staff/{user}', [AdminAttendanceController::class, 'staff'])
            ->name('attendance.staff');

        Route::get('/attendance/staff/{user}/date/{date}', [AdminAttendanceController::class, 'showByDate'])
            ->name('attendance.staff.showByDate');

        Route::get('/attendance/staff/{user}/csv', [AdminAttendanceController::class, 'csv'])
            ->name('attendance.staff.csv');

        Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])
            ->name('correction.list');

        Route::get('/stamp_correction_request/approve/{changeRequest}', [CorrectionRequestController::class, 'show'])
            ->name('correction.approve.show');

        Route::post('/stamp_correction_request/approve/{changeRequest}', [CorrectionRequestController::class, 'approve'])
            ->name('correction.approve');

        Route::post('/logout', [AdminLoginController::class, 'logout'])
            ->name('logout');
    });
});
