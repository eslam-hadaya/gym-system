<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPlanController extends Controller
{
    public function index(): Response
    {
        $plans = Plan::withCount([
            'subscriptions as active_members' => function ($query) {
                $query->currentForAccess();
            },
        ])
            ->orderBy('days')
            ->get()
            ->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'days' => $plan->days,
                'price' => (int) $plan->price,
                'is_active' => (bool) $plan->is_active,
                'active_members' => (int) $plan->active_members,
            ]);

        return Inertia::render('Admin/Dashboard/Plans', [
            'plans' => $plans,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        Plan::create($validated);

        return back()->with('status', 'plan-created');
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $plan->update($validated);

        return back()->with('status', 'plan-updated');
    }
}
