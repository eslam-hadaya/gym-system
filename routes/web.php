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
Route::get("/", function () {
    $plans = Plan::where("is_active", true)
        ->orderBy("days")
        ->get(["id", "name", "days", "price", "is_active"]);

    return Inertia::render("Welcome", [
        "plans" => $plans,
    ]);
})->name("home");

/*
|--------------------------------------------------------------------------
| مسارات الزوار فقط (Guest Routes)
|--------------------------------------------------------------------------
*/
Route::middleware("guest")->group(function () {
    Route::get("/login", [AuthController::class, "showLogin"])->name("login");
    Route::post("/login", [AuthController::class, "login"]);

    Route::get("/register", [AuthController::class, "showRegister"])->name(
        "register",
    );
    Route::post("/register", [AuthController::class, "register"]);
});

/*
|--------------------------------------------------------------------------
| مسارات الجلسة والمصادقة العامة
|--------------------------------------------------------------------------
*/
Route::middleware("auth")->group(function () {
    Route::get("/verify-email", [AuthController::class, "showVerifyOtp"])->name(
        "verification.notice",
    );
    Route::post("/verify-email", [AuthController::class, "verifyOtp"])->name(
        "verification.verify",
    );
    Route::post("/verify-email/resend", [AuthController::class, "resendOtp"])
        ->middleware("throttle:3,5")
        ->name("verification.resend");

    Route::post("/logout", [AuthController::class, "logout"])->name("logout");
});

/*
|--------------------------------------------------------------------------
| بوابة إدارة النظام (Admin Only)
|--------------------------------------------------------------------------
*/
Route::prefix("admin/dashboard")
    ->middleware(["auth", "role:admin"])
    ->name("admin.")
    ->group(function () {
        Route::get("/", [AdminDashboardController::class, "index"])->name(
            "dashboard",
        );

        // إدارة أعضاء واشتراكات
        Route::get("/members", [AdminMemberController::class, "index"])->name(
            "members",
        );
        Route::post("/members/{member}/cancel-subscription", [
            AdminMemberController::class,
            "cancelSubscription",
        ])->name("members.cancel-subscription");

        // العمليات السريعة على الاشتراكات
        Route::post("/subscription/{subscription}/approve", [
            AdminDashboardController::class,
            "approveSubscription",
        ])->name("subscription.approve");
        Route::post("/subscription/{subscription}/reject", [
            AdminDashboardController::class,
            "rejectSubscription",
        ])->name("subscription.reject");
        Route::post("/subscription/{subscription}/cancel", [
            AdminDashboardController::class,
            "cancelSubscription",
        ])->name("subscription.cancel");

        // مكتب الاستقبال وقارئ الـ QR
        Route::get("/checkin", [AdminCheckInController::class, "index"])->name(
            "checkin",
        );
        Route::post("/checkin/scan", [
            AdminCheckInController::class,
            "scan",
        ])->name("checkin.scan");
        Route::post("/checkin/requests/{checkIn}/approve", [
            AdminCheckInController::class,
            "approveRequest",
        ])->name("checkin.approve");
        Route::post("/checkin/requests/{checkIn}/reject", [
            AdminCheckInController::class,
            "rejectRequest",
        ])->name("checkin.reject");

        // الباقات والأسعار
        Route::get("/plans", [AdminPlanController::class, "index"])->name(
            "plans",
        );
        Route::post("/plans", [AdminPlanController::class, "store"])->name(
            "plans.store",
        );
        Route::put("/plans/{plan}", [
            AdminPlanController::class,
            "update",
        ])->name("plans.update");

        // الصندوق وشام كاش
        Route::get("/finance", [AdminFinanceController::class, "index"])->name(
            "finance",
        );
        Route::post("/finance/verify/{subscription}", [
            AdminFinanceController::class,
            "verify",
        ])->name("finance.verify");
        Route::post("/finance/reject/{subscription}", [
            AdminFinanceController::class,
            "reject",
        ])->name("finance.reject");
    });

/*
|--------------------------------------------------------------------------
| بوابة اللاعبين (Member Only)
|--------------------------------------------------------------------------
*/
Route::prefix("member/dashboard")
    ->middleware(["auth", "role:member"])
    ->name("member.")
    ->group(function () {
        Route::get("/", [MemberDashboardController::class, "index"])->name(
            "dashboard",
        );

        // قراءة الإشعارات
        Route::post("/notifications/{id}/read", function ($id) {
            auth()->user()->unreadNotifications->where("id", $id)->markAsRead();
            return back();
        })->name("notifications.read");

        Route::post("/notifications/read-all", function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back();
        })->name("notifications.readAll");

        // طلب تسجيل دخول للتمرين
        Route::post("/checkin-request", [
            MemberDashboardController::class,
            "requestCheckIn",
        ])->name("checkin.request");

        // شاشة الاشتراك والتجديد
        Route::get("/subscriptions", [
            MemberDashboardController::class,
            "subscriptions",
        ])->name("subscriptions");
        Route::post("/subscriptions", [
            MemberDashboardController::class,
            "storeSubscription",
        ])->name("subscriptions.store");
        Route::post("/subscriptions/cancel", [
            MemberDashboardController::class,
            "cancelSubscription",
        ])->name("subscriptions.cancel");

        // سجل الحضور والتمارين
        Route::get("/history", [
            MemberDashboardController::class,
            "history",
        ])->name("history");
    });
