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
        return Inertia::render('Auth/Login');
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^09[0-9]{8}$/', 'unique:members,phone'],
            'gender' => ['required', 'in:male,female'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'member',
            ]);

            $nextId = (Member::max('id') ?? 0) + 1001;

            Member::create([
                'user_id' => $newUser->id,
                'member_code' => 'GYM-' . $nextId,
                'phone' => $validated['phone'],
                'gender' => $validated['gender'],
                'status' => 'pending',
            ]);

            return $newUser;
        });

        $this->dispatchOtp($user);
        $request->session()->put('otp_verify_user_id', $user->id);

        return redirect()->route('verification.notice');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'بيانات الاعتماد المدخلة غير مطابقة لسجلاتنا.',
            ]);
        }

        if (is_null($user->email_verified_at)) {
            $this->dispatchOtp($user);
            $request->session()->put('otp_verify_user_id', $user->id);

            return redirect()->route('verification.notice');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    public function showVerifyOtp(): Response|\Illuminate\Http\RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // إذا كان الحساب موثقاً أصلاً، يُوجّه إلى مكانه فوراً
        if ($user->email_verified_at) {
            return $user->role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('member.dashboard');
        }

        return Inertia::render('Auth/VerifyOtp', [
            'email' => $user->email,
        ]);
    }
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('otp_verify_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $cachedOtp = Cache::get($this->otpCacheKey($user->id));

        if (! $cachedOtp || $cachedOtp !== $request->input('code')) {
            throw ValidationException::withMessages([
                'code' => 'رمز التحقق غير صحيح أو انتهت صلاحيته (5 دقائق).',
            ]);
        }

        Cache::forget($this->otpCacheKey($user->id));
        $request->session()->forget('otp_verify_user_id');

        if (is_null($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('otp_verify_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $this->dispatchOtp($user);

        return back()->with('status', 'otp-sent');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dispatchOtp(User $user): void
    {
        $otpCode = (string) random_int(100000, 999999);

        Cache::put($this->otpCacheKey($user->id), $otpCode, now()->addMinutes(5));

        Mail::to($user->email)->send(new OtpMail($otpCode, $user->name));
    }

    private function otpCacheKey(int $userId): string
    {
        return "auth_otp_user_{$userId}";
    }

    private function redirectByRole(User $user): RedirectResponse
    {
        return $user->role === 'admin'
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('member.dashboard'));
    }
}
