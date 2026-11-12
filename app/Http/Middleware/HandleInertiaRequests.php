<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * الـ Root View الرئيسي للواجهة
     *
     * @var string
     */
    protected $rootView = "app";

    /**
     * تحديد إصدار الأصول للكاش
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * البيانات المشتركة مع جميع واجهات Inertia
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            "auth" => [
                "user" => $user,
            ],
            "flash" => [
                "status" => fn() => $request->session()->get("status"),
                "error" => fn() => $request->session()->get("error"),
            ],
        ];
    }
}
