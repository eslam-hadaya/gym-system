<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminMemberController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $status = $request->query('status', 'all');

        $query = Member::with(['user', 'activeSubscription.plan'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('member_code', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        if (in_array($status, ['active', 'expired', 'pending'], true)) {
            $query->where('status', $status);
        }

        $members = $query->paginate(15)->withQueryString()->through(function ($member) {
            $activeSub = $member->activeSubscription;

            return [
                'id' => $member->id,
                'name' => $member->user->name ?? 'غير محدد',
                'phone' => $member->phone,
                'code' => $member->member_code,
                'gender' => $member->gender === 'female' ? 'أنثى' : 'ذكر',
                'plan' => $activeSub?->plan?->name ?? 'لم يشترك بعد',
                'status' => $member->status,
                'expiry' => $activeSub?->end_date ? $activeSub->end_date->format('Y-m-d') : '—',
            ];
        });

        return Inertia::render('Admin/Dashboard/Members', [
            'members' => $members,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status,
            ],
        ]);
    }
}
