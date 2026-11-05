<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. الواجهة الترحيبية
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// 2. شاشات المصادقة
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->name('register');

Route::get('/verify-email', function () {
    return Inertia::render('Auth/VerifyEmail', [
        'email' => session('email', 'player@example.com'),
    ]);
})->name('verification.notice');

// 3. أقسام لوحة الإدارة (Admin Dashboard)
Route::prefix('admin/dashboard')->group(function () {
    Route::get('/', function () { return Inertia::render('Admin/Dashboard/Index'); });
    Route::get('/checkin', function () { return Inertia::render('Admin/Dashboard/CheckInDesk'); });
    Route::get('/members', function () { return Inertia::render('Admin/Dashboard/Members'); });
    Route::get('/plans', function () { return Inertia::render('Admin/Dashboard/Plans'); });
    Route::get('/finance', function () { return Inertia::render('Admin/Dashboard/Finance'); });
});

// 4. أقسام بوابة اللاعب (Member Portal)
Route::prefix('member/dashboard')->group(function () {
    Route::get('/', function () { return Inertia::render('Member/Dashboard/Index'); });
    Route::get('/subscriptions', function () { return Inertia::render('Member/Dashboard/Subscriptions'); });
    Route::get('/history', function () { return Inertia::render('Member/Dashboard/History'); });
});
