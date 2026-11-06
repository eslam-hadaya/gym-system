<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCheckInController extends Controller
{
    public function index(): Response
    {
        $todayLogs = CheckIn::with(['member.user', 'member.activeSubscription.plan'])
            ->whereDate('created_at', today())
            ->latest()
            ->take(20)
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'name' => $entry->member->user->name ?? 'غير محدد',
                'code' => $entry->member->member_code,
                'plan' => $entry->member->activeSubscription->plan->name ?? 'بدون باقة',
                'time' => $entry->created_at->format('h:i A'),
                'gender' => $entry->member->gender === 'female' ? 'أنثى' : 'ذكر',
                'status' => $entry->status,
                'note' => $entry->note,
            ]);

        $validCount = CheckIn::whereDate('created_at', today())
            ->where('status', 'valid')
            ->count();

        $rejectedCount = CheckIn::whereDate('created_at', today())
            ->where('status', '!=', 'valid')
            ->count();

        return Inertia::render('Admin/Dashboard/CheckInDesk', [
            'todayLogs' => $todayLogs,
            'stats' => [
                'total_today' => $todayLogs->count(),
                'valid_count' => $validCount,
                'rejected_count' => $rejectedCount,
            ],
        ]);
    }

    public function scan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_code' => ['required', 'string'],
        ]);

        $code = trim($validated['member_code']);
        $member = Member::with(['user', 'activeSubscription'])->where('member_code', $code)->first();

        if (! $member) {
            return back()->withErrors(['member_code' => 'كود العضوية غير موجود بالنظام']);
        }

        $activeSub = $member->activeSubscription;
        $status = 'valid';
        $note = 'دخول نظامي عبر المسح';

        if (! $activeSub || ! $activeSub->end_date || $activeSub->end_date->isPast()) {
            $status = 'expired';
            $note = 'اشتراك منتهٍ أو غير مفعل';
        }

        CheckIn::create([
            'member_id' => $member->id,
            'status' => $status,
            'note' => $note,
        ]);

        return back()->with('scan_result', [
            'name' => $member->user->name,
            'code' => $member->member_code,
            'status' => $status,
            'note' => $note,
        ]);
    }
}
