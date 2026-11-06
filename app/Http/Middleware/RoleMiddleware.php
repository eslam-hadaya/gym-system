<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. التحقق من وجود جلسة دخول
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        // 2. التحقق من توثيق البريد الإلكتروني بحسابات اللاعبين
        if ($user->role === 'member' && ! $user->email_verified_at) {
            return redirect()->route('verification.notice');
        }

        // 3. التحقق الصارم من تطابق الدور
        if ($user->role !== $role) {
            // توجيه المخالف قسرياً إلى بوابته الشرعية
            return $user->role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('member.dashboard');
        }

        return $next($request);
    }
}
