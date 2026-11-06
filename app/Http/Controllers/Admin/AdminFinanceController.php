<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminFinanceController extends Controller
{
    public function index(Request $request): Response
    {
        $method = $request->query('method', 'all');

        $query = Subscription::with(['member.user', 'plan'])->latest();

        if (in_array($method, ['cash', 'shamcash'], true)) {
            $query->where('payment_method', $method);
        }

        $transactions = $query->paginate(15)->through(fn ($sub) => [
            'id' => 'TXN-' . str_pad($sub->id, 5, '0', STR_PAD_LEFT),
            'subscription_id' => $sub->id,
            'member' => $sub->member->user->name ?? 'غير محدد',
            'member_code' => $sub->member->member_code ?? '—',
            'plan_name' => $sub->plan->name ?? 'باقة ملغاة',
            'amount' => (int) $sub->price_paid,
            'method' => $sub->payment_method,
            'ref' => $sub->transaction_ref ?: ($sub->payment_method === 'cash' ? 'مكتب الكاشير' : '—'),
            'date' => $sub->created_at->format('Y-m-d H:i'),
            'status' => $sub->payment_status,
        ]);

        $verifiedSubs = Subscription::where('payment_status', 'verified');

        $totalIncome = (clone $verifiedSubs)->sum('price_paid');
        $shamCashIncome = (clone $verifiedSubs)->where('payment_method', 'shamcash')->sum('price_paid');
        $cashIncome = (clone $verifiedSubs)->where('payment_method', 'cash')->sum('price_paid');

        return Inertia::render('Admin/Dashboard/Finance', [
            'transactions' => $transactions,
            'currentMethod' => $method,
            'stats' => [
                'total_income' => (int) $totalIncome,
                'shamcash_income' => (int) $shamCashIncome,
                'cash_income' => (int) $cashIncome,
            ],
        ]);
    }

    public function verify(Subscription $subscription): RedirectResponse
    {
        if ($subscription->payment_status === 'verified') {
            return back()->with('status', 'already-verified');
        }

        DB::transaction(function () use ($subscription) {
            $planDays = $subscription->plan->days ?? 30;
            $startDate = now();
            $endDate = now()->addDays($planDays);

            $subscription->update([
                'payment_status' => 'verified',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            $subscription->member->update([
                'status' => 'active',
            ]);
        });

        return back()->with('status', 'subscription-verified');
    }
}
