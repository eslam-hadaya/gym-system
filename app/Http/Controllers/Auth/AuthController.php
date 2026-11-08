<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render("Auth/Login");
    }

    public function showRegister(): Response
    {
        return Inertia::render("Auth/Register");
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "email" => [
                "required",
                "string",
                "email",
                "max:255",
                "unique:users,email",
            ],
            "phone" => [
                "required",
                "string",
                'regex:/^09[0-9]{8}$/',
                "unique:members,phone",
            ],
            "gender" => ["required", "in:male,female"],
            "password" => ["required", "string", "min:8", "confirmed"],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $newUser = User::create([
                "name" => $validated["name"],
                "email" => $validated["email"],
                "password" => Hash::make($validated["password"]),
                "role" => "member",
            ]);

            $nextId = (Member::max("id") ?? 0) + 1001;

            Member::create([
                "user_id" => $newUser->id,
                "member_code" => "GYM-" . $nextId,
                "phone" => $validated["phone"],
                "gender" => $validated["gender"],
                "status" => "pending",
            ]);

            return $newUser;
        });

        // 1. تسجيل دخول المستخدم في الجلسة فوراً
        Auth::login($user);
        $request->session()->regenerate();

        // 2. إرسال رمز التحقق وحفظ معرف المستخدم
        $this->dispatchOtp($user);
        $request->session()->put("otp_verify_user_id", $user->id);

        // 3. التوجيه المباشر لصفحة التحقق من الإيميل
        return redirect()->route("verification.notice");
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required", "string"],
            "remember" => ["boolean"],
        ]);

        $user = User::where("email", $credentials["email"])->first();

        if (!$user || !Hash::check($credentials["password"], $user->password)) {
            throw ValidationException::withMessages([
                "email" => "بيانات الاعتماد المدخلة غير مطابقة لسجلاتنا.",
            ]);
        }

        // تسجيل دخوله أولاً
        Auth::login($user, $request->boolean("remember"));
        $request->session()->regenerate();

        // إذا كان البريد غير موثق، أرسل الكود وحوّله مباشرة لصفحة إدخال الرمز
        if (is_null($user->email_verified_at)) {
            $this->dispatchOtp($user);
            $request->session()->put("otp_verify_user_id", $user->id);

            return redirect()->route("verification.notice");
        }

        return $this->redirectByRole($user);
    }

    public function showVerifyOtp(Request $request): Response|RedirectResponse
    {
        // التحقق من هوية المستخدم سواء عبر تسجيل الدخول أو الـ session
        $user = Auth::user();
        if (!$user) {
            $userId = $request->session()->get("otp_verify_user_id");
            $user = $userId ? User::find($userId) : null;
        }

        if (!$user) {
            return redirect()->route("login");
        }

        // إذا كان الحساب موثقاً مسبقاً، لا داعي لصفحة التحقق
        if ($user->email_verified_at) {
            return $this->redirectByRole($user);
        }

        return Inertia::render("Auth/VerifyOtp", [
            "email" => $user->email,
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            "code" => ["required", "string", "size:6"],
        ]);

        $user = Auth::user();
        if (!$user) {
            $userId = $request->session()->get("otp_verify_user_id");
            $user = $userId ? User::findOrFail($userId) : null;
        }

        if (!$user) {
            return redirect()->route("login");
        }

        $cachedOtp = Cache::get($this->otpCacheKey($user->id));

        if (!$cachedOtp || $cachedOtp !== $request->input("code")) {
            throw ValidationException::withMessages([
                "code" => "رمز التحقق غير صحيح أو انتهت صلاحيته (5 دقائق).",
            ]);
        }

        Cache::forget($this->otpCacheKey($user->id));
        $request->session()->forget("otp_verify_user_id");

        // توثيق الحساب
        if (is_null($user->email_verified_at)) {
            $user->forceFill(["email_verified_at" => now()])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            $userId = $request->session()->get("otp_verify_user_id");
            $user = $userId ? User::findOrFail($userId) : null;
        }

        if (!$user) {
            return redirect()->route("login");
        }

        $this->dispatchOtp($user);

        return back()->with("status", "otp-sent");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard("web")->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("login");
    }

    private function dispatchOtp(User $user): void
    {
        $otpCode = (string) random_int(100000, 999999);

        Cache::put(
            $this->otpCacheKey($user->id),
            $otpCode,
            now()->addMinutes(5),
        );

        try {
            Mail::to($user->email)->send(new OtpMail($otpCode, $user->name));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::info(
                "OTP Code for {$user->email}: {$otpCode}",
            );
        }
    }

    private function otpCacheKey(int $userId): string
    {
        return "auth_otp_user_{$userId}";
    }

    private function redirectByRole(User $user): RedirectResponse
    {
        return $user->role === "admin"
            ? redirect()->intended(route("admin.dashboard"))
            : redirect()->intended(route("member.dashboard"));
    }
}
