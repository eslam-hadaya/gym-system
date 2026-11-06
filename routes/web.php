<?php

use App\Http\Controllers\Admin\AdminCheckInController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminMemberController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Member\MemberDashboardController;
use App\Models\Plan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| المسارات العامة (Public Routes)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $plans = Plan::where('is_active', true)
        ->orderBy('days')
        ->get(['id', 'name', 'days', 'price', 'is_active']);

    return Inertia::render('Welcome', [
        'plans' => $plans,
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| مسارات الزوار فقط (Guest Routes)
| ممنوع دخول أي مسجل دخول، وسيطرد تلقائياً إلى لوحة تحكمه
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| مسارات توثيق البريد وتأكيد الـ OTP
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [AuthController::class, 'showVerifyOtp'])->name('verification.notice');
    Route::post('/verify-email', [AuthController::class, 'verifyOtp'])->name('verification.verify');
    Route::post('/verify-email/resend', [AuthController::class, 'resendOtp'])
        ->middleware('throttle:3,5')
        ->name('verification.resend');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| بوابة إدارة النظام (Admin Only)
| محصنة بالـ Auth و role:admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin/dashboard')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // العمليات السريعة على الاشتراكات
        Route::post('/subscription/{subscription}/approve', [AdminDashboardController::class, 'approveSubscription'])->name('admin.subscription.approve');
        Route::post('/subscription/{subscription}/reject', [AdminDashboardController::class, 'rejectSubscription'])->name('admin.subscription.reject');

        // مكتب الاستقبال وقارئ الـ QR
        Route::get('/checkin', [AdminCheckInController::class, 'index'])->name('admin.checkin');
        Route::post('/checkin/scan', [AdminCheckInController::class, 'scan'])->name('admin.checkin.scan');

        // إدارة اللاعبين
        Route::get('/members', [AdminMemberController::class, 'index'])->name('admin.members');

        // الباقات والأسعار
        Route::get('/plans', [AdminPlanController::class, 'index'])->name('admin.plans');
        Route::post('/plans', [AdminPlanController::class, 'store'])->name('admin.plans.store');
        Route::put('/plans/{plan}', [AdminPlanController::class, 'update'])->name('admin.plans.update');

        // الصندوق وشام كاش
        Route::get('/finance', [AdminFinanceController::class, 'index'])->name('admin.finance');
        Route::post('/finance/verify/{subscription}', [AdminFinanceController::class, 'verify'])->name('admin.finance.verify');
    });

/*
|--------------------------------------------------------------------------
| بوابة اللاعبين (Member Only)
| محصنة بالـ Auth و role:member والتحقق من الـ OTP
|--------------------------------------------------------------------------
*/
Route::prefix('member/dashboard')
    ->middleware(['auth', 'role:member'])
    ->group(function () {
        // بطاقة العضوية والـ QR
        Route::get('/', [MemberDashboardController::class, 'index'])->name('member.dashboard');

        // صفحة الاشتراك والتجديد (شام كاش / كاش)
       Route::get('/subscriptions', [MemberDashboardController::class, 'subscriptions'])->name('member.subscriptions');

        // سجل الحضور والتمارين
        Route::get('/history', [MemberDashboardController::class, 'history'])->name('member.history');
    });
